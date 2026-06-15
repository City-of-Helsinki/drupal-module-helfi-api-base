<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\helfi_api_base\AuditLog\AuditLogEntityType;
use Drupal\helfi_api_base\AuditLog\AuditLogOperation;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Audit log hooks.
 */
final readonly class AuditLogHooks {

  /**
   * The configured entity type matchers.
   *
   * @var array<\Drupal\helfi_api_base\AuditLog\AuditLogEntityType>
   */
  private array $loggedEntityTypes;

  public function __construct(
    private AuditLogServiceInterface $auditLogService,
    #[Autowire(param: 'helfi_api_base.audit_log_entity_types')]
    array $loggedEntityTypes = [],
  ) {
    $this->loggedEntityTypes = array_map(
      AuditLogEntityType::fromArray(...),
      $loggedEntityTypes,
    );
  }

  /**
   * Implements hook_user_login().
   */
  #[Hook('user_login')]
  public function onUserLogin(AccountInterface $account): void {
    $this->auditLogService->logOperation(new AuditLogEvent(
      operation: 'DRUPAL_LOGIN',
      message: sprintf('User "%s" (UID: %d) logged in', $account->getAccountName(), $account->id()),
      target: [
        'id' => $account->id(),
        'type' => 'USER',
        'name' => $account->getAccountName(),
      ],
    ));
  }

  /**
   * Implements hook_user_logout().
   */
  #[Hook('user_logout')]
  public function onUserLogout(AccountInterface $account): void {
    $this->auditLogService->logOperation(new AuditLogEvent(
      operation: 'DRUPAL_LOGOUT',
      message: sprintf('User "%s" (UID: %d) logged out', $account->getAccountName(), $account->id()),
      target: [
        'id' => $account->id(),
        'type' => 'USER',
        'name' => $account->getAccountName(),
      ],
    ));
  }

  /**
   * Implements hook_entity_view().
   */
  #[Hook('entity_view')]
  public function onEntityView(array &$build, EntityInterface $entity): void {
    $this->logEntityOperation($entity, AuditLogOperation::Read);
  }

  /**
   * Implements hook_entity_insert().
   */
  #[Hook('entity_insert')]
  public function onEntityInsert(EntityInterface $entity): void {
    $this->logEntityOperation($entity, AuditLogOperation::Create);
  }

  /**
   * Implements hook_entity_update().
   */
  #[Hook('entity_update')]
  public function onEntityUpdate(EntityInterface $entity): void {
    $this->logEntityOperation($entity, AuditLogOperation::Update);
  }

  /**
   * Implements hook_entity_delete().
   */
  #[Hook('entity_delete')]
  public function onEntityDelete(EntityInterface $entity): void {
    $this->logEntityOperation($entity, AuditLogOperation::Delete);
  }

  /**
   * Logs an audit event for an entity operation, if the entity is loggable.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operation was performed on.
   * @param \Drupal\helfi_api_base\AuditLog\AuditLogOperation $operation
   *   The operation performed.
   */
  private function logEntityOperation(EntityInterface $entity, AuditLogOperation $operation): void {
    foreach ($this->loggedEntityTypes as $entityType) {
      if ($entityType->isLoggable($entity, $operation)) {
        $this->logEvent($entity, $operation);
        return;
      }
    }
  }

  /**
   * Writes an audit event for an entity operation.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operation was performed on.
   * @param \Drupal\helfi_api_base\AuditLog\AuditLogOperation $operation
   *   The operation performed.
   */
  private function logEvent(EntityInterface $entity, AuditLogOperation $operation): void {
    $this->auditLogService->logOperation(new AuditLogEvent(
      operation: $operation->value,
      message: sprintf(
        '%s operation on %s entity (ID: %s)',
        $operation->value,
        $entity->getEntityTypeId(),
        $entity->id(),
      ),
      target: [
        'id' => $entity->id(),
        'type' => mb_strtoupper($entity->bundle()),
        'name' => (string) $entity->label(),
      ],
    ));
  }

}

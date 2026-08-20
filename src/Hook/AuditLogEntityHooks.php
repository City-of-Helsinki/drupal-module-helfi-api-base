<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Hook;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\diff\DiffEntityComparison;
use Drupal\helfi_api_base\AuditLog\AuditLogEntityType;
use Drupal\helfi_api_base\AuditLog\AuditLogOperation;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use ResilientLogger\Utils\HumanReadableDiffer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Audit log entity hooks.
 */
final class AuditLogEntityHooks {

  /**
   * The configured entity type matchers.
   *
   * @var array<string,\Drupal\helfi_api_base\AuditLog\AuditLogEntityType>
   */
  private array $loggedEntityTypes;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface $auditLogService
   *   The audit log service.
   * @param \Drupal\diff\DiffEntityComparison $entityComparison
   *   The entity differ service.
   * @param array<string,array{entity_type: string, bundle?: string, operations?: array<string>}> $loggedEntityTypes
   *   The configured entity type matchers.
   */
  public function __construct(
    private readonly AuditLogServiceInterface $auditLogService,
    #[Autowire(service: 'diff.entity_comparison')] private readonly DiffEntityComparison $entityComparison,
    #[Autowire(param: 'helfi_api_base.audit_log_entity_types')] array $loggedEntityTypes = [],
  ) {
    foreach ($loggedEntityTypes as $type) {
      $entityType = AuditLogEntityType::fromArray($type);
      $this->loggedEntityTypes[$entityType->entityType] = $entityType;
    }
  }

  /**
   * Implements hook_entity_view().
   *
   * @param array<string, mixed> $build
   *   The renderable array representing the entity content.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being viewed.
   */
  #[Hook('entity_view')]
  public function onEntityView(array &$build, EntityInterface $entity): void {
    if (!$this->isLoggable($entity, AuditLogOperation::EntityRead)) {
      return;
    }
    $this->logEvent($entity, AuditLogOperation::EntityRead);
  }

  /**
   * Implements hook_entity_insert().
   */
  #[Hook('entity_insert')]
  public function onEntityInsert(EntityInterface $entity): void {
    if (!$this->isLoggable($entity, AuditLogOperation::EntityCreate)) {
      return;
    }
    $this->logEvent($entity, AuditLogOperation::EntityCreate);
  }

  /**
   * Generates a diff between the updated and previous entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to compare.
   *
   * @return null|array<string,string>
   *   The diff.
   */
  private function getEntityDiff(ContentEntityInterface $entity): ?array {
    if (!$previousEntity = $entity->getOriginal()) {
      return NULL;
    }
    $previousEntity = $previousEntity->getTranslation($entity->language()->getId());

    $differ = new HumanReadableDiffer();

    $fields = $this->entityComparison->compareRevisions($previousEntity, $entity);

    $changed = [];
    foreach ($fields as $key => $field) {
      if ($field['#data']['#left'] === $field['#data']['#right']) {
        continue;
      }
      $changed[$key] = $differ->diff($field['#data']['#left'], $field['#data']['#right']);
    }
    return $changed;
  }

  /**
   * Implements hook_entity_update().
   */
  #[Hook('entity_update')]
  public function onEntityUpdate(EntityInterface $entity): void {
    if (!$this->isLoggable($entity, AuditLogOperation::EntityUpdate)) {
      return;
    }
    $extra = [];

    if ($entity instanceof ContentEntityInterface && $entity->getEntityType()->isRevisionable()) {
      $extra['ContentDiff'] = $this->getEntityDiff($entity) ?? '';
    }
    $this->logEvent($entity, AuditLogOperation::EntityUpdate, $extra);
  }

  /**
   * Implements hook_entity_delete().
   */
  #[Hook('entity_delete')]
  public function onEntityDelete(EntityInterface $entity): void {
    if (!$this->isLoggable($entity, AuditLogOperation::EntityDelete)) {
      return;
    }
    $this->logEvent($entity, AuditLogOperation::EntityDelete);
  }

  /**
   * Checks if the given entity type is loggable.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operation was performed on.
   * @param \Drupal\helfi_api_base\AuditLog\AuditLogOperation $operation
   *   The operation performed.
   *
   * @return bool
   *   TRUE if the entity type is loggable.
   */
  private function isLoggable(EntityInterface $entity, AuditLogOperation $operation): bool {
    if (!$type = $this->loggedEntityTypes[$entity->getEntityTypeId()] ?? NULL) {
      return FALSE;
    }
    return $type->isLoggable($entity, $operation);
  }

  /**
   * Writes an audit event for an entity operation.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operation was performed on.
   * @param \Drupal\helfi_api_base\AuditLog\AuditLogOperation $operation
   *   The operation performed.
   * @param array<mixed> $extra
   *   The extra data.
   */
  private function logEvent(EntityInterface $entity, AuditLogOperation $operation, array $extra = []): void {
    $this->auditLogService->logOperation(new AuditLogEvent(
      operation: $operation,
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
      extra: $extra,
    ));
  }

}

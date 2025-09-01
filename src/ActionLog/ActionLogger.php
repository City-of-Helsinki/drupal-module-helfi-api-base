<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ActionLog;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Action logger service.
 */
class ActionLogger {

  /**
   * Default types that are always logged.
   *
   * Use `helfi_api_base.action_log_entities` parameter
   * for additional entities.
   */
  const DEFAULT_TYPES = [
    'node',
    'media',
    'taxonomy_term',
    'tpr_unit',
    'tpr_service',
    'tpr_errand_service',
    'redirect',
    'user',
  ];

  /**
   * Allowed types.
   */
  private readonly array $allowedTypes;

  public function __construct(
    #[Autowire(service: 'logger.channel.helfi_api_base')]
    private readonly LoggerInterface $logger,
    #[Autowire(param: 'helfi_api_base.action_log_entities')]
    readonly array $actionLogEntities,
    private readonly AccountProxyInterface $currentUser,
  ) {
    $this->allowedTypes = array_merge(self::DEFAULT_TYPES, $actionLogEntities);
  }

  /**
   * Logs system actions.
   *
   * @param string $verb
   *   Action being performed.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Subject entity.
   */
  public function log(string $verb, EntityInterface $entity): void {
    // Logging _everything_ is too noisy. E.g. saving a
    // node might result in dozens of paragraph saves.
    if (!in_array($entity->getEntityTypeId(), $this->allowedTypes ?? [])) {
      return;
    }

    // Most likely in a migration, executing a drush command, etc.
    if (!$this->currentUser->isAuthenticated()) {
      return;
    }

    // Log who modified what, so it is possible to audit edits later.
    $this->logger->info('@verb @entity_type:@entity_id by user @user_id', [
      '@verb' => $verb,
      '@entity_type' => $entity->getEntityTypeId(),
      '@entity_id' => $entity->id(),
      '@user_id' => $this->currentUser->id(),
    ]);
  }

}

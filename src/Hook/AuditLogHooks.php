<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\helfi_api_base\ActionLog\DTO\EntityUpdateAction;
use Drupal\helfi_api_base\ActionLog\QueueProcessor;

/**
 * Audit log entity hooks.
 */
final class AuditLogHooks {

  public function __construct(
    private readonly QueueProcessor $queueProcessor,
  ) {

  }

  /**
   * Queues the updated entity for audit log.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The updated entity.
   */
  #[Hook('entity_update')]
  public function update(EntityInterface $entity): void {
    $allowed = [
      'node',
    ];

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    if (!in_array($entity->getEntityTypeId(), $allowed)) {
      return;
    }
    $this->queueProcessor->queue(new EntityUpdateAction(
      id: (int) $entity->id(),
      previousRevision: (int) $entity->getOriginal()->getRevisionId(),
      entity_type: $entity->getEntityTypeId(),
      langcode: $entity->language()->getId(),
    ));
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ActionLog;

use Drupal\Core\DestructableInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\diff\DiffEntityComparison;
use Drupal\helfi_api_base\ActionLog\DTO\EntityUpdateAction;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * A queue processor for ActionLog items.
 */
final class QueueProcessor implements DestructableInterface {

  /**
   * The entity queue.
   *
   * @var array<\Drupal\helfi_api_base\ActionLog\DTO\EntityUpdateAction>
   */
  private array $queue = [];

  public function __construct(
    #[Autowire(service: 'diff.entity_comparison')] private readonly DiffEntityComparison $entityComparison,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * Queues the given item for processing.
   *
   * @param \Drupal\helfi_api_base\ActionLog\DTO\EntityUpdateAction $item
   *   The item to queue.
   */
  public function queue(EntityUpdateAction $item): void {
    $this->queue[] = $item;
  }

  /**
   * Processes the given item.
   *
   * @param \Drupal\helfi_api_base\ActionLog\DTO\EntityUpdateAction $item
   *   The item to process.
   */
  private function processEntity(EntityUpdateAction $item): void {
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage($item->entity_type);
    $entity = $storage->load($item->id)
      ->getTranslation($item->langcode);

    if ($previousEntity = $storage->loadRevision($item->previousRevision)) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $previousEntity */
      $previousEntity->getTranslation($item->langcode);
    }
    $fields = $this->entityComparison->compareRevisions($previousEntity, $entity);

    $changed = [];
    foreach ($fields as $key => $field) {
      if ($field['#data']['#left'] === $field['#data']['#right']) {
        continue;
      }
      $changed[$key] = $field;
    }
    // @todo do something with the diff.
  }

  /**
   * {@inheritdoc}
   */
  public function destruct(): void {
    foreach ($this->queue as $item) {
      $this->processEntity($item);
    }
  }

}

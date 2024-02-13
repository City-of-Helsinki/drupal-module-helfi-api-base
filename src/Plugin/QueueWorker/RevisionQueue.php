<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\helfi_api_base\Entity\Revision\RevisionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles old revision deletion.
 *
 * @QueueWorker(
 *  id = "helfi_api_base_revision",
 *  title = @Translation("Queue worker for deleting old revisions"),
 *  cron = {"time" = 180}
 * )
 */
final class RevisionQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The revision manager.
   *
   * @var \Drupal\helfi_api_base\Entity\Revision\RevisionManager
   */
  private RevisionManager $revisionManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) : self {
    $instance = new self($configuration, $plugin_id, $plugin_definition);
    $instance->revisionManager = $container->get('helfi_api_base.revision_manager');
    return $instance;
  }

  /**
   * Process queue item.
   *
   * @param array|mixed $data
   *   The queue data. Should contain 'entity_id' and 'entity_type'.
   */
  public function processItem($data) : void {
    if (!isset($data['entity_id'], $data['entity_type'])) {
      return;
    }
    ['entity_id' => $id, 'entity_type' => $type] = $data;

    $revisions = $this->revisionManager->getRevisions($type, $id);

    if ($revisions) {
      $this->revisionManager->deleteRevisions($type, $revisions);
    }
  }

}

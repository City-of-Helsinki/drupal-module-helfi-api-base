<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\helfi_api_base\Entity\Revision\RevisionManager;
use Drush\Attributes\Command;
use Drush\Attributes\Option;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * A drush command file to manage revisions.
 */
final class RevisionCommands extends DrushCommands {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Entity\Revision\RevisionManager $revisionManager
   *   The revision manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection service.
   */
  public function __construct(
    private readonly RevisionManager $revisionManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $connection,
  ) {
  }

  /**
   * Deletes the old revisions.
   *
   * @param string $entityType
   *   The entity type.
   * @param int|null $entityId
   *   The entity ID.
   * @param array $options
   *   The options.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:revision:delete')]
  #[Option(name: 'id', description: 'The entity ID')]
  #[Option(name: 'keep', description: 'Number of revisions to keep')]
  public function delete(
    string $entityType,
    ?int $entityId = NULL,
    array $options = [
      'keep' => NULL,
    ],
  ) : int {
    if (!$this->revisionManager->entityTypeIsSupported($entityType)) {
      $this->io()->writeln('Given entity type is not supported.');

      return DrushCommands::EXIT_FAILURE;
    }

    $definition = $this->entityTypeManager->getDefinition($entityType);
    $query = $this->connection->select($definition->getBaseTable(), 't')
      ->fields('t', [$definition->getKey('id')]);

    if ($entityId) {
      $query->condition($definition->getKey('id'), $entityId);
    }
    $entityIds = $query
      ->execute()
      ->fetchCol();

    $progressBar = new ProgressBar($this->io(), count($entityIds));
    $progressBar->start();

    foreach ($entityIds as $id) {
      $revisions = $this
        ->revisionManager
        ->getRevisions($entityType, $id, $options['keep']);

      $this->revisionManager->deleteRevisions($entityType, $revisions);
      $progressBar->advance();
    }
    $progressBar->finish();

    return DrushCommands::EXIT_SUCCESS;
  }

}

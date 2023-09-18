<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\helfi_api_base\Entity\Revision\RevisionManager;
use Drush\Attributes\Command;
use Drush\Attributes\Option;
use Drush\Commands\DrushCommands;

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
   * @param array $options
   *   The options.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:revision:delete')]
  #[Option(name: 'keep', description: 'Number of revisions to keep')]
  public function delete(string $entityType, array $options = ['keep' => RevisionManager::KEEP_REVISIONS]) : int {
    if (!$this->revisionManager->entityTypeIsSupported($entityType)) {
      $this->io()->writeln('Given entity type is not supported.');
      return DrushCommands::EXIT_SUCCESS;
    }

    $definition = $this->entityTypeManager->getDefinition($entityType);
    $entityIds = $this->connection->select($definition->getBaseTable(), 't')
      ->fields('t', [$definition->getKey('id')])
      ->execute()
      ->fetchCol();

    $totalEntities = $remainingEntities = count($entityIds);
    $this->io()->writeln((string) new FormattableMarkup('Found @count @type entities', [
      '@count' => $totalEntities,
      '@type' => $entityType,
    ]));

    foreach ($entityIds as $id) {
      $revisions = $this->revisionManager->getRevisions($entityType, $id, $options['keep']);
      $revisionCount = count($revisions);

      $message = sprintf('Entity has less than %s revisions. Skipping', $options['keep']);

      if ($revisionCount > 0) {
        $message = (string) new FormattableMarkup('Deleting @count revisions', ['@count' => $revisionCount]);
      }
      $this->io()->writeln((string) new FormattableMarkup('[@current/@entities] @message ...', [
        '@current' => $remainingEntities--,
        '@entities' => $totalEntities,
        '@message' => $message,
      ]));
      $this->revisionManager->deleteRevisions($entityType, $revisions);
    }

    return DrushCommands::EXIT_SUCCESS;
  }

}

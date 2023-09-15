<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Entity\Revision;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A class to manage revisions.
 */
final class RevisionManager {

  public const KEEP_REVISIONS = 5;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly Connection $connection,
  ) {
  }

  /**
   * Gets the supported entity types.
   *
   * @return array
   *   An array of entity types.
   */
  public function getSupportedEntityTypes() : array {
    return $this->configFactory
      ->get('helfi_api_base.delete_revisions')
      ->get('entity_types') ?? [];
  }

  /**
   * Asserts that entity type is supported.
   *
   * @param string $entityType
   *   The entity type to check.
   */
  private function assertEntityType(string $entityType) : void {
    if (!in_array($entityType, $this->getSupportedEntityTypes())) {
      throw new \InvalidArgumentException('Entity type is not supported.');
    }
    try {
      $definition = $this->entityTypeManager->getDefinition($entityType);

      if (!$definition->isRevisionable()) {
        throw new \InvalidArgumentException('Entity type does not support revisions.');
      }
    }
    catch (PluginNotFoundException $e) {
      throw new \InvalidArgumentException('Invalid entity type.', previous: $e);
    }
  }

  /**
   * Checks whether the given entity type is supported or not.
   *
   * @param string $entityType
   *   The entity type to check.
   *
   * @return bool
   *   TRUE if the entity type is supported.
   */
  public function entityTypeIsSupported(string $entityType) : bool {
    try {
      $this->assertEntityType($entityType);

      return TRUE;
    }
    catch (\InvalidArgumentException) {
    }
    return FALSE;
  }

  /**
   * Deletes the previous revisions for the given entity type and ids.
   *
   * @param string $entityType
   *   The entity type.
   * @param array $ids
   *   The version ids.
   */
  public function deleteRevisions(string $entityType, array $ids) : void {
    $this->assertEntityType($entityType);

    $storage = $this->entityTypeManager
      ->getStorage($entityType);

    foreach ($ids as $id) {
      $storage->deleteRevision($id);
    }
  }

  /**
   * Gets the revisions for given entity type and id.
   *
   * Grouped by language to make testing easier.
   *
   * @param string $entityType
   *   The entity type.
   * @param string|int $id
   *   The entity id.
   * @param int $keep
   *   The number of revisions to keep.
   *
   * @return array
   *   An array of revision IDs.
   */
  public function getRevisionsPerLanguage(
    string $entityType,
    string|int $id,
    int $keep = self::KEEP_REVISIONS
  ) : array {
    $this->assertEntityType($entityType);

    $storage = $this->entityTypeManager->getStorage($entityType);
    $definition = $this->entityTypeManager->getDefinition($entityType);

    $revision_ids = $this->connection->query(
      (string) new FormattableMarkup('SELECT [@vid] FROM {@table} WHERE [@id] = :id ORDER BY [@vid]', [
        '@vid' => $definition->getKey('revision'),
        '@table' => $definition->getRevisionTable(),
        '@id' => $definition->getKey('id'),
      ]),
      [':id' => $id]
    )->fetchCol();

    $revisions = [];

    if (count($revision_ids) === 0) {
      return [];
    }
    krsort($revision_ids);

    foreach ($revision_ids as $vid) {
      /** @var \Drupal\Core\Entity\RevisionableInterface $revision */
      $revision = $storage->loadRevision($vid);

      foreach ($revision->getTranslationLanguages() as $langcode => $language) {
        if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
          $revisions[$langcode][] = $revision->getLoadedRevisionId();
        }
      }
    }

    foreach ($revisions as $langcode => $items) {
      $revisions[$langcode] = array_slice($items, $keep);
    }

    return $revisions;
  }

  /**
   * Gets revisions for given entity type and id.
   *
   * @param string $entityType
   *   The entity type.
   * @param string|int $id
   *   The entity ID.
   * @param int $keep
   *   The number of revisions to keep.
   *
   * @return array
   *   An array of revision ids.
   */
  public function getRevisions(string $entityType, string|int $id, int $keep = self::KEEP_REVISIONS) : array {
    $revisions = $this->getRevisionsPerLanguage($entityType, $id, $keep);

    return array_unique(array_merge(...array_values($revisions)));
  }

}

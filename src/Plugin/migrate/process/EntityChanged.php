<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\migrate\process;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\helfi_api_base\MigrateTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A process plugin to allow us to skip entities that have not changed.
 *
 * @MigrateProcessPlugin(
 *   id = "entity_has_changed"
 * )
 *
 * @code
 * process:
 *   changed:
 *     -
 *       plugin: format_date
 *       source: changed
 *       from_format: 'Y-m-d\TH:i:s'
 *       to_format: 'U'
 *       from_timezone: 'Europe/Helsinki'
 *       to_timezone: 'UTC'
 *     -
 *       plugin: entity_has_changed
 *       entity_type: trp_unit
 * @endcode
 */
final class EntityChanged extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  use MigrateTrait;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $entityStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition, MigrationInterface $migration = NULL) {
    $instance = new static($configuration, $pluginId, $pluginDefinition);

    $instance->entityStorage = $container->get('entity_type.manager')
      ->getStorage($configuration['entity_type']);

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    ['sourceid1' => $id] = $row->getIdMap() + ['sourceid1' => NULL];

    $value = (int) $value;

    /** @var \Drupal\helfi_api_base\Entity\RemoteEntityBase $entity */
    if (!$id || $value <= 0 || !$entity = $this->entityStorage->load($id)) {
      return $value;
    }

    // Ignore importing completely if entity has not changed and we're doing
    // a partial migrate.
    if ((int) $entity->getChangedTime() >= $value && $this->isPartialMigrate()) {
      throw new MigrateSkipRowException();
    }

    return $value;
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\migrate\process;

use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration = NULL
  ) : self {
    $instance = new self($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    ['sourceid1' => $id] = $row->getIdMap() + ['sourceid1' => NULL];

    $value = (int) $value;

    $storage = $this->entityTypeManager->getStorage($this->configuration['entity_type']);

    /** @var \Drupal\helfi_api_base\Entity\RemoteEntityBase $entity */
    if (!$id || $value <= 0 || !$entity = $storage->load($id)) {
      return $value;
    }

    if (!$entity->hasField('changed')) {
      return $value;
    }

    // Ignore importing completely if entity has not changed and we're doing
    // a partial migrate.
    if ((int) $entity->get('changed')->value >= $value && $this->isPartialMigrate()) {
      throw new MigrateSkipRowException();
    }

    return $value;
  }

}

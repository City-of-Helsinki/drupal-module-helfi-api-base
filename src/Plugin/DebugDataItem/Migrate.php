<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\DebugDataItem;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\helfi_api_base\DebugDataItemPluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the debug_data_item.
 *
 * @DebugDataItem(
 *   id = "migrate",
 *   label = @Translation("Migrate"),
 *   description = @Translation("Migrate data")
 * )
 */
final class Migrate extends DebugDataItemPluginBase implements ContainerFactoryPluginInterface, CacheableDependencyInterface {

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  private MigrationPluginManagerInterface $migrationPluginManager;

  /**
   * The key value storage.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  private KeyValueStoreInterface $keyValue;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) : self {
    $instance = new self($configuration, $plugin_id, $plugin_definition);
    $instance->migrationPluginManager = $container->get('plugin.manager.migration');
    $instance->keyValue = $container->get('keyvalue')
      ->get('migrate_last_imported');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(): array {
    $data = [];
    foreach ($this->migrationPluginManager->createInstances([]) as $migration) {
      $data[] = [
        'id' => $migration->id(),
        'status' => $migration->getStatusLabel(),
        'last_imported' => $this->keyValue->get($migration->id(), -1),
        'imported' => $migration->getIdMap()->importedCount(),
      ];
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() : array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() : array {
    // Add migrations as dependencies. These are cleared in
    // \Drupal\helfi_api_base\EventSubscriber\MigrationSubscriber::onPreImport().
    return array_map(function (MigrationInterface $migration) {
      return 'migration:' . $migration->id();
    }, $this->migrationPluginManager->createInstances([]));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() : int {
    return CacheBackendInterface::CACHE_PERMANENT;
  }

}

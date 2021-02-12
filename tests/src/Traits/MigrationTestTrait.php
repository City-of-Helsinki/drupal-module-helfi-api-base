<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\Migration;

/**
 * Provides shared functionality for api tests.
 */
trait MigrationTestTrait {

  /**
   * A two dimensional array of messages.
   *
   * The first key is the type of message, the second is just numeric. Values
   * are the messages.
   *
   * @var null|array
   */
  protected ?array $migrateMessages = [];

  /**
   * Flushes all plugin caches.
   */
  protected function flushPluginCache() : void {
    $this->container->get('plugin.cache_clearer')->clearCachedDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function display($message, $type = 'status') {
    $this->assertTrue($type == 'status', (string) $message);
  }

  /**
   * Executes a single migration.
   *
   * @param string $migration
   *   The migration ID.
   * @param array $configuration
   *   The migration configuration.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function executeMigration(string $migration, array $configuration = []) : void {
    $migration = $this->getMigration($migration, $configuration);

    (new MigrateExecutable($migration, $this))->import();
  }

  /**
   * Gets the migration plugin.
   *
   * @param string $plugin_id
   *   The plugin ID of the migration to get.
   * @param array $configuration
   *   The migration configuration.
   *
   * @return \Drupal\migrate\Plugin\Migration
   *   The migration plugin.
   */
  protected function getMigration(string $plugin_id, array $configuration = []) : Migration {
    return $this->container->get('plugin.manager.migration')->createInstance($plugin_id, $configuration);
  }

}

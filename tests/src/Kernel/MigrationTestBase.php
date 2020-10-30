<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessageInterface;

/**
 * Base class for migration tests.
 */
abstract class MigrationTestBase extends ApiKernelTestBase implements MigrateMessageInterface {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'migrate_plus',
    'language',
    'content_translation',
  ];

  /**
   * A two dimensional array of messages.
   *
   * The first key is the type of message, the second is just numeric. Values
   * are the messages.
   *
   * @var array
   */
  protected ?array $migrateMessages = [];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig(['language', 'content_translation', 'migrate_plus']);

    foreach (['fi', 'sv'] as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function display($message, $type = 'status') {
    $this->assert($type == 'status', $message, 'migrate');
  }

  /**
   * Executes a single migration.
   *
   * @param string $migration
   *   The migration ID.
   */
  protected function executeMigration(string $migration) {
    $migration = $this->getMigration($migration);

    (new MigrateExecutable($migration, $this))->import();
  }

  /**
   * Gets the migration plugin.
   *
   * @param string $plugin_id
   *   The plugin ID of the migration to get.
   *
   * @return \Drupal\migrate\Plugin\Migration
   *   The migration plugin.
   */
  protected function getMigration(string $plugin_id) {
    return $this->container->get('plugin.manager.migration')->createInstance($plugin_id);
  }

}

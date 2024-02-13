<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Plugin\DebugDataItem;

use Drupal\Tests\helfi_api_base\Kernel\MigrationTestBase;

/**
 * Tests Migrate debug data plugin.
 *
 * @group helfi_api_base
 */
class MigrateDebugDataItemTest extends MigrationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'link',
    'remote_entity_test',
    'menu_link_content',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('remote_entity_test');
  }

  /**
   * Tests that composer plugin collects data properly.
   */
  public function testCompile() : void {
    /** @var \Drupal\helfi_api_base\DebugDataItemPluginManager $manager */
    $manager = $this->container->get('plugin.manager.debug_data_item');
    /** @var \Drupal\helfi_api_base\Plugin\DebugDataItem\Composer $plugin */
    $plugin = $manager->createInstance('migrate');

    // Make sure dummy migrate is empty.
    $this->assertEquals([
      'id' => 'dummy_migrate',
      'status' => 'Idle',
      'last_imported' => -1,
      'imported' => 0,
    ], $plugin->collect()[0]);

    // Run migrate to confirm migrate data changes.
    $this->executeMigration('dummy_migrate');
    // We use core's MigrateExecutable to run migrations in tests, and
    // 'migrate_last_imported' is set in drush's MigrateCommands, so we
    // can't actually test it here.
    $this->assertEquals([
      'id' => 'dummy_migrate',
      'status' => 'Idle',
      'last_imported' => -1,
      'imported' => 4,
    ], $plugin->collect()[0]);
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel\Plugin\DebugDataItem;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Maintenance mode debug data plugin.
 *
 * @group helfi_api_base
 */
class MaintenanceModeTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * Tests that composer plugin collects data properly.
   *
   * @covers \Drupal\helfi_api_base\Plugin\DebugDataItem\MaintenanceMode::collect
   * @covers \Drupal\helfi_api_base\Plugin\DebugDataItem\MaintenanceMode::create
   */
  public function testCompile() : void {
    /** @var \Drupal\helfi_api_base\DebugDataItemPluginManager $manager */
    $manager = $this->container->get('plugin.manager.debug_data_item');
    /** @var \Drupal\helfi_api_base\Plugin\DebugDataItem\MaintenanceMode $plugin */
    $plugin = $manager->createInstance('maintenance_mode');
    \Drupal::state()->set('system.maintenance_mode', TRUE);
    $this->assertEquals(['maintenance_mode' => TRUE], $plugin->collect());

    \Drupal::state()->set('system.maintenance_mode', FALSE);
    $this->assertEquals(['maintenance_mode' => FALSE], $plugin->collect());
  }

}

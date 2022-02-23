<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Composer debug data plugin.
 *
 * @group helfi_api_base
 */
class ComposerDebugDataItemTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * Tests that composer plugin collects data properly.
   */
  public function testCompile() : void {
    /** @var \Drupal\helfi_api_base\DebugDataItemPluginManager $manager */
    $manager = $this->container->get('plugin.manager.debug_data_item');
    /** @var \Drupal\helfi_api_base\Plugin\DebugDataItem\Composer $plugin */
    $plugin = $manager->createInstance('composer');
    // Make sure we have at least one package.
    $this->assertNotEmpty($plugin->collect()['packages'][0]['name']);
  }

}

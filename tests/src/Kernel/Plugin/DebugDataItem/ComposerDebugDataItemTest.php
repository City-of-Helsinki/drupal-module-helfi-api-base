<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Plugin\DebugDataItem;

use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests Composer debug data plugin.
 */
#[Group('helfi_api_base')]
#[RunTestsInSeparateProcesses]
class ComposerDebugDataItemTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'composer_lock_test',
  ];

  /**
   * Tests that the plugin collects data properly.
   */
  public function testCompile() : void {
    /** @var \Drupal\helfi_api_base\DebugDataItemPluginManager $manager */
    $manager = $this->container->get('plugin.manager.debug_data_item');
    /** @var \Drupal\helfi_api_base\Plugin\DebugDataItem\Composer $plugin */
    $plugin = $manager->createInstance('composer');
    $this->assertNotEmpty($plugin->label());
    $this->assertEquals([], $plugin->calculateDependencies());

    $build = $plugin->collect();

    $count = 0;
    foreach ($build['packages'] as $package) {
      $this->assertNotEmpty($package['name']);
      $this->assertInstanceOf(Url::class, $package['releases_url']);
      $count++;
    }

    $this->assertTrue($count > 0);
  }

}

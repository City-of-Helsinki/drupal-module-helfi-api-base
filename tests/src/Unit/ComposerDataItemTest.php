<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit;

use ComposerLockParser\ComposerInfo;
use ComposerLockParser\Package;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\helfi_api_base\Plugin\DebugDataItem\Composer;
use Drupal\Tests\UnitTestCase;

/**
 * Tests Composer plugin.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\DebugDataItem\Composer
 * @group helfi_api_base
 */
class ComposerDataItemTest extends UnitTestCase {

  /**
   * Creates a new container builder.
   *
   * @param array $packages
   *   The expected packages.
   *
   * @return \Drupal\Core\DependencyInjection\ContainerBuilder
   *   The container builder.
   */
  private function getContainer(array $packages) : ContainerBuilder {
    $composerInfo = $this->prophesize(ComposerInfo::class);
    $composerInfo
      ->getPackages()
      ->willReturn(array_map(
        fn(array $package) => Package::factory($package),
        $packages
      ));

    $container = new ContainerBuilder();
    $container->set('helfi_api_base.composer_info', $composerInfo->reveal());
    return $container;
  }

  /**
   * Tests collect method.
   *
   * @dataProvider collectionDataProvider
   */
  public function testCollect(array $expected, array $data) : void {
    $container = $this->getContainer($data);
    $composer = Composer::create($container, [], 'composer', []);
    $this->assertEquals($expected, $composer->collect());
  }

  /**
   * Data provider for testCollect().
   *
   * @return array[]
   *   The data.
   */
  public function collectionDataProvider() : array {
    return [
      [
        [
          'packages' => [
            [
              'name' => 'drupal/helfi_tpr',
              'version' => '2.0.1',
              'time' => '2022-01-31T13:21:47+00:00',
            ],
            [
              'name' => 'drupal/helfi_hdbt_admin',
              'version' => '2.0.2',
              'time' => '2022-01-30T10:21:47+00:00',
            ],
          ],
        ],
        [
          [
            'name' => 'drupal/helfi_tpr',
            'version' => '2.0.1',
            'time' => '2022-01-31T13:21:47+00:00',
          ],
          [
            'name' => 'drupal/helfi_hdbt_admin',
            'version' => '2.0.2',
            'time' => '2022-01-30T10:21:47+00:00',
          ],
          [
            'name' => 'drupal/random_package',
            'version' => '2.1.1',
          ],
        ],
      ],
    ];
  }

}

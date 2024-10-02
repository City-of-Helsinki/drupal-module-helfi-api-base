<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Plugin\DebugDataItem\Migrate;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests Migrate plugin.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\DebugDataItem\Migrate
 * @group helfi_api_base
 */
class MigrateDataItemTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Gets the sut.
   *
   * @return \Drupal\helfi_api_base\Plugin\DebugDataItem\Migrate
   *   The sut.
   */
  private function getSut() : Migrate {
    $idMap = $this->prophesize(MigrateIdMapInterface::class);
    $idMap->importedCount()
      ->willReturn(234);
    $migration = $this->prophesize(MigrationInterface::class);
    $migration->id()
      ->shouldBeCalled()
      ->willReturn('test_migration');
    $migration->getStatusLabel()
      ->willReturn('idle');
    $migration->getIdMap()
      ->willReturn($idMap->reveal());

    $pluginManager = $this->prophesize(MigrationPluginManager::class);
    $pluginManager->createInstances([])
      ->shouldBeCalled()
      ->willReturn([$migration->reveal()]);

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore
      ->get(Argument::any(), Argument::any())
      ->willReturn(123);
    $keyValue = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValue->get('migrate_last_imported')
      ->shouldBeCalled()
      ->willReturn($keyValueStore->reveal());
    $container = new ContainerBuilder();
    $container->set('keyvalue', $keyValue->reveal());
    $container->set('plugin.manager.migration', $pluginManager->reveal());

    return Migrate::create($container, [], 'plugin', []);
  }

  /**
   * @covers ::create
   * @covers ::collect
   */
  public function testCollection() : void {
    $this->assertEquals([
      [
        'id' => 'test_migration',
        'status' => 'idle',
        'last_imported' => 123,
        'imported' => 234,
      ],
    ], $this->getSut()->collect());
  }

  /**
   * @covers ::create
   * @covers ::getCacheContexts
   * @covers ::getCacheTags
   * @covers ::getCacheMaxAge
   */
  public function testCacheableMetadata() : void {
    $sut = $this->getSut();
    $this->assertNotEmpty($sut->getCacheMaxAge());
    $this->assertTrue(is_array($sut->getCacheContexts()));
    $this->assertContains('migration:test_migration', $sut->getCacheTags());
  }

}

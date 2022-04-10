<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\DebugDataItemInterface;
use Drupal\helfi_api_base\DebugDataItemPluginManager;
use Drupal\helfi_api_base\Plugin\rest\resource\DebugDataResource;
use Drupal\rest\ResourceResponse;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests DebugDataResource dependency map.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\rest\resource\DebugDataResource
 * @group helfi_api_base
 */
class DebugDataResourceTest extends UnitTestCase {

  /**
   * Constructs a fake data item plugin.
   *
   * @param array $dependencies
   *   The dependencies.
   *
   * @return \Drupal\helfi_api_base\DebugDataItemInterface
   *   The fake debug data plugin.
   */
  private function getDataItemPlugin(array $dependencies) : DebugDataItemInterface {
    return new class ($dependencies) implements DebugDataItemInterface, CacheableDependencyInterface {

      /**
       * Constructs a new object.
       *
       * @param array $dependencies
       *   The dependencies.
       */
      public function __construct(private array $dependencies) {
      }

      /**
       * {@inheritdoc}
       */
      public function label(): string {
        return 'test';
      }

      /**
       * {@inheritdoc}
       */
      public function collect(): array {
        return [];
      }

      /**
       * {@inheritdoc}
       */
      public function calculateDependencies() {
        return $this->dependencies;
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
        return [];
      }

      /**
       * {@inheritdoc}
       */
      public function getCacheMaxAge() : int {
        return -1;
      }

    };
  }

  /**
   * Gets the sut.
   *
   * @return \Drupal\helfi_api_base\Plugin\rest\resource\DebugDataResource
   *   The sut.
   */
  private function getSut() : DebugDataResource {
    $pluginManager = $this->prophesize(DebugDataItemPluginManager::class);
    $pluginManager->getDefinitions()
      ->willReturn([
        ['id' => 'test', 'label' => new TranslatableMarkup('Test')],
        ['id' => 'test2', 'label' => 'Test 2'],
      ]);
    $pluginManager->createInstance('test')
      ->willReturn($this->getDataItemPlugin([
        'config' => ['user.role.anonymous'],
        'theme' => ['seven'],
        'content' => ['node:article:f0a189e6-55fb-47fb-8005-5bef81c44d6d'],
        'module' => ['node'],
      ]));
    $pluginManager->createInstance('test2')
      ->willReturn($this->getDataItemPlugin([
        'config' => ['user.role.authenticated'],
        'module' => ['helfi_api_base'],
      ]));
    $logger = $this->prophesize(LoggerInterface::class);
    $loggerFactory = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactory->get('rest')
      ->willReturn($logger->reveal());
    $container = new ContainerBuilder();
    $container->setParameter('serializer.formats', ['json']);
    $container->set('logger.factory', $loggerFactory->reveal());
    $container->set('plugin.manager.debug_data_item', $pluginManager->reveal());

    return DebugDataResource::create($container, [], 'plugin', []);
  }

  /**
   * Tests that dependencies are populated.
   *
   * @covers ::calculateDependencies
   * @covers ::create
   * @covers ::getDataPlugins
   */
  public function testDependencies() : void {
    // Make sure multiple dependencies are merged together.
    $this->assertEquals([
      'config' => ['user.role.anonymous', 'user.role.authenticated'],
      'theme' => ['seven'],
      'content' => ['node:article:f0a189e6-55fb-47fb-8005-5bef81c44d6d'],
      'module' => ['user', 'node', 'helfi_api_base'],
    ], $this->getSut()->calculateDependencies());
  }

  /**
   * Tests that cacheable metadata is added.
   *
   * @covers ::get
   * @covers ::create
   * @covers ::getDAtaPlugins
   */
  public function testCacheableMetadata() : void {
    $response = $this->getSut()->get();
    $this->assertInstanceOf(ResourceResponse::class, $response);
    $this->assertEquals(
      CacheBackendInterface::CACHE_PERMANENT,
      $response->getCacheableMetadata()->getCacheMaxAge()
    );
  }

}

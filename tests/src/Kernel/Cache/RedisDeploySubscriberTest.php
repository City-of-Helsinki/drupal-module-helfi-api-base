<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Cache;

use Drupal\helfi_api_base\Cache\RedisDeploySubscriber;
use Drupal\helfi_api_base\Event\PostDeployEvent;
use Drupal\KernelTests\KernelTestBase;
use Drupal\redis\Cache\CacheBackendFactory;
use Drupal\Tests\redis\Traits\RedisTestInterfaceTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests Redis cache post deploy subscriber.
 */
#[RunTestsInSeparateProcesses]
#[Group('helfi_api_base')]
class RedisDeploySubscriberTest extends KernelTestBase {

  use ProphecyTrait;
  use RedisTestInterfaceTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'system',
    'redis',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->setUpSettings();
  }

  /**
   * {@inheritdoc}
   */
  protected function bootKernel(): void {
    $this->setSetting('redis.connection', [
      'interface' => 'PhpRedis',
      'host' => 'redis',
      'port' => 6379,
    ]);
    parent::bootKernel();
  }

  /**
   * Gets the redis client.
   *
   * @return \Redis
   *   The client.
   */
  private function getRedisClient(): \Redis {
    $factory = $this->container->get('redis.factory');

    return $factory->getClient();
  }

  /**
   * Tests that the service is registered.
   */
  #[Test]
  public function testServiceProvider() : void {
    $this->assertTrue($this->container->hasDefinition(RedisDeploySubscriber::class));
    $this->assertTrue($this->getRedisClient()->ping());
  }

  /**
   * Tests that event subscriber is run.
   */
  #[Test]
  public function testEventSubscriber(): void {
    $service = $this->container->get('cache.backend.redis');
    $this->assertInstanceOf(CacheBackendFactory::class, $service);

    $cache = $service->get('default');
    $cache->set('test_key', 'value');
    $data = $cache->get('test_key');

    $this->assertEquals('value', $data->data);

    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $service */
    $service = $this->container->get('event_dispatcher');
    $service->dispatch(new PostDeployEvent());

    $this->assertFalse($cache->get('test_key'));
  }

}

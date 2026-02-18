<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Cache;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\CacheTagInvalidator;
use Drupal\helfi_api_base\Azure\PubSub\PubSubClientFactoryInterface;
use Drupal\helfi_api_base\Azure\PubSub\PubSubManager;
use Drupal\helfi_api_base\Azure\PubSub\Settings;
use Drupal\helfi_api_base\Cache\CacheTagInvalidator as CacheTagInvalidatorService;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use WebSocket\Client;
use WebSocket\Message\Text;

/**
 * Tests Cache invalidator subscriber.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Cache\CacheTagInvalidator
 * @group helfi_api_base
 */
class CacheTagInvalidatorTest extends KernelTestBase {

  use ProphecyTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->config('helfi_api_base.api_accounts')
      ->set('vault', [
        [
          'id' => 'pubsub',
          'plugin' => 'json',
          'data' => json_encode([
            'endpoint' => 'localhost',
            'hub' => 'local',
            'group' => 'invalidate_cache',
            'access_key' => '123',
          ]),
        ],
      ])
      ->save();
  }

  /**
   * @covers ::__construct
   * @covers ::invalidateTags
   * @covers \Drupal\helfi_api_base\EventSubscriber\CacheTagInvalidatorSubscriber::onReceive
   * @covers \Drupal\helfi_api_base\EventSubscriber\CacheTagInvalidatorSubscriber::getSubscribedEvents
   */
  public function testInvalidateTags() : void {
    // Override the cache_tags.invalidator service, so we can verify that
    // the event subscriber is actually run and the invalidation logic is
    // actually triggered.
    // @see \Drupal\helfi_api_base\EventSubscriber\CacheTagInvalidatorSubscriber
    $cacheTagInvalidator = new CacheTagInvalidator();
    $this->container->set('cache_tags.invalidator', $cacheTagInvalidator);

    $client = $this->prophesize(Client::class);
    $client->text(Argument::any());
    $client->receive()->willReturn(
      new Text('{"type":"event","event":"connected"}'),
      new Text('{"data": {"tags":["node:123"]}}'),
    );
    $clientFactory = $this->prophesize(PubSubClientFactoryInterface::class);
    $clientFactory->create('123')->willReturn($client->reveal());

    $pubSubManager = new PubSubManager(
      $clientFactory->reveal(),
      $this->container->get('event_dispatcher'),
      $this->container->get('datetime.time'),
      $this->container->get(Settings::class),
      $this->prophesize(LoggerInterface::class)->reveal(),
    );
    $this->container->set(PubSubManager::class, $pubSubManager);
    $pubSubManager->receive();

    /** @var \Drupal\helfi_api_base\Cache\CacheTagInvalidator $sut */
    $sut = $this->container->get(CacheTagInvalidatorService::class);
    $sut->invalidateTags(['node:123']);
    $this->assertArrayHasKey('node:123', $cacheTagInvalidator->tags);
  }

}

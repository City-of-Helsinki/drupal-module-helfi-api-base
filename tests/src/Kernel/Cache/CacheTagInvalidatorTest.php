<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel\Cache;

use Drupal\helfi_api_base\Azure\PubSub\PubSubManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\CacheTagInvalidatorTrait;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use WebSocket\Client;

/**
 * Tests Cache invalidator subscriber.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Cache\CacheTagInvalidator
 * @group helfi_api_base
 */
class CacheTagInvalidatorTest extends KernelTestBase {

  use ProphecyTrait;
  use CacheTagInvalidatorTrait;

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

    $this->config('helfi_api_base.pubsub.settings')
      ->set('endpoint', 'wss://localhost')
      ->set('hub', 'hub')
      ->set('group', 'group')
      ->set('access_token', '123')
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
    $cacheTagInvalidator = $this->mockCacheInvalidator();
    $this->container->set('cache_tags.invalidator', $cacheTagInvalidator);

    $client = $this->prophesize(Client::class);
    $client->text(Argument::any());
    $client->receive()->willReturn(
      '{"type":"event","event":"connected"}',
      '{"data": {"tags":["node:123"]}}'
    );
    $pubSubManager = new PubSubManager(
      $client->reveal(),
      $this->container->get('event_dispatcher'),
      $this->container->get('datetime.time'),
      $this->container->get('helfi_api_base.pubsub_settings'),
    );
    $this->container->set('helfi_api_base.pubsub_manager', $pubSubManager);
    $pubSubManager->receive();

    /** @var \Drupal\helfi_api_base\Cache\CacheTagInvalidator $sut */
    $sut = $this->container->get('helfi_api_base.cache_tag_invalidator');
    $sut->invalidateTags(['node:123']);
    $this->assertArrayHasKey('node:123', $cacheTagInvalidator->tags);
  }

}

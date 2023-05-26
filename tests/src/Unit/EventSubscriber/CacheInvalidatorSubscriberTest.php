<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\helfi_api_base\Azure\PubSub\PubSubMessage;
use Drupal\helfi_api_base\EventSubscriber\CacheInvalidatorSubscriber;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\EventSubscriber\CacheInvalidatorSubscriber
 * @group helfi_api_base
 */
class CacheInvalidatorSubscriberTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * @covers ::getSubscribedEvents
   */
  public function testEvents() : void {
    $this->assertIsArray(CacheInvalidatorSubscriber::getSubscribedEvents());
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testInvalidCacheTags() : void {
    $invalidator = $this->prophesize(CacheTagsInvalidatorInterface::class);
    $invalidator->invalidateTags(Argument::any())->shouldNotBeCalled();
    $sut = new CacheInvalidatorSubscriber($invalidator->reveal());
    $sut->onReceive(new PubSubMessage([]));
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testValidCacheTags() : void {
    $invalidator = $this->prophesize(CacheTagsInvalidatorInterface::class);
    $invalidator->invalidateTags(['node:123'])->shouldBeCalled();
    $sut = new CacheInvalidatorSubscriber($invalidator->reveal());
    $sut->onReceive(new PubSubMessage(['tags' => ['node:123']]));
  }

}

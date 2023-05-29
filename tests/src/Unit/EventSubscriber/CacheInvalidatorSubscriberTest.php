<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\helfi_api_base\Azure\PubSub\PubSubMessage;
use Drupal\helfi_api_base\EventSubscriber\CacheTagInvalidatorSubscriber;
use Drupal\Tests\helfi_api_base\Traits\CacheTagInvalidatorTrait;
use Drupal\Tests\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\EventSubscriber\CacheTagInvalidatorSubscriber
 * @group helfi_api_base
 */
class CacheInvalidatorSubscriberTest extends UnitTestCase {

  use ProphecyTrait;
  use CacheTagInvalidatorTrait;

  /**
   * @covers ::getSubscribedEvents
   */
  public function testEvents() : void {
    $this->assertIsArray(CacheTagInvalidatorSubscriber::getSubscribedEvents());
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testInvalidServiceException() : void {
    // CacheTagsInvalidatorInterface does not have ::resetChecksums method.
    // Make sure exception is thrown unless we use the default core service.
    $this->expectException(\LogicException::class);
    $invalidator = $this->prophesize(CacheTagsInvalidatorInterface::class);
    $invalidator->invalidateTags(['node:123'])->shouldBeCalled();
    $sut = new CacheTagInvalidatorSubscriber($invalidator->reveal());
    $sut->onReceive(new PubSubMessage(['data' => ['tags' => ['node:123']]]));
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testInvalidCacheTags() : void {
    $mock = $this->mockCacheInvalidator();
    $sut = new CacheTagInvalidatorSubscriber($mock);
    $sut->onReceive(new PubSubMessage([]));
    $this->assertEmpty($mock->tags);
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testValidCacheTags() : void {
    $mock = $this->mockCacheInvalidator();
    $sut = new CacheTagInvalidatorSubscriber($mock);
    $sut->onReceive(new PubSubMessage(['data' => ['tags' => ['node:123']]]));

    $this->assertArrayHasKey('node:123', $mock->tags);
    $this->assertEquals(1, $mock->checkSumResets);
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\Tests\helfi_api_base\Traits\CacheTagInvalidator;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Drupal\helfi_api_base\Event\CacheTagInvalidateEvent;
use Drupal\helfi_api_base\Azure\PubSub\PubSubMessage;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\helfi_api_base\EventSubscriber\CacheTagInvalidatorSubscriber;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\EventSubscriber\CacheTagInvalidatorSubscriber
 * @group helfi_api_base
 */
class CacheInvalidatorSubscriberTest extends UnitTestCase {

  use ProphecyTrait;
  use EnvironmentResolverTrait;

  /**
   * @covers ::getSubscribedEvents
   */
  public function testEvents() : void {
    $this->assertIsArray(CacheTagInvalidatorSubscriber::getSubscribedEvents());
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers ::isValidInstance
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testInvalidServiceException() : void {
    // CacheTagsInvalidatorInterface does not have ::resetChecksums method.
    // Make sure exception is thrown unless we use the default core service.
    $this->expectException(\LogicException::class);
    $invalidator = $this->prophesize(CacheTagsInvalidatorInterface::class);
    $invalidator->invalidateTags(['node:123'])->shouldBeCalled();
    $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    $eventDispatcher->dispatch(Argument::any(), Argument::any())
      ->willReturn(new CacheTagInvalidateEvent());
    $environmentResolver = $this->getEnvironmentResolver();
    $sut = new CacheTagInvalidatorSubscriber($invalidator->reveal(), $environmentResolver, $eventDispatcher->reveal());
    $sut->onReceive(new PubSubMessage(['data' => ['tags' => ['node:123']]]));
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers ::isValidInstance
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testInvalidProject() : void {
    // Make sure a project is considered valid if environment resolver
    // fails to find an active project.
    $mock = new CacheTagInvalidator();
    $environmentResolver = $this->getEnvironmentResolver('invalid_project');
    $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    $eventDispatcher->dispatch(Argument::any(), Argument::any())
      ->willReturn(new CacheTagInvalidateEvent());
    $sut = new CacheTagInvalidatorSubscriber($mock, $environmentResolver, $eventDispatcher->reveal());
    $sut->onReceive(new PubSubMessage([
      'data' => [
        'tags' => ['node:123'],
        'instances' => [Project::ASUMINEN],
      ],
    ]));
    $this->assertArrayHasKey('node:123', $mock->tags);
    $this->assertEquals(1, $mock->checkSumResets);
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers ::isValidInstance
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testInvalidCacheTags() : void {
    $mock = new CacheTagInvalidator();
    $environmentResolver = $this->getEnvironmentResolver();
    $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    $eventDispatcher->dispatch(Argument::any(), Argument::any())
      ->willReturn(new CacheTagInvalidateEvent());
    $sut = new CacheTagInvalidatorSubscriber($mock, $environmentResolver, $eventDispatcher->reveal());
    $sut->onReceive(new PubSubMessage([]));
    $this->assertEmpty($mock->tags);
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers ::isValidInstance
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testValidCacheTags() : void {
    $mock = new CacheTagInvalidator();
    $environmentResolver = $this->getEnvironmentResolver();
    $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    $eventDispatcher->dispatch(Argument::any(), Argument::any())
      ->willReturn(new CacheTagInvalidateEvent());
    $sut = new CacheTagInvalidatorSubscriber($mock, $environmentResolver, $eventDispatcher->reveal());
    $sut->onReceive(new PubSubMessage(['data' => ['tags' => ['node:123']]]));

    $this->assertArrayHasKey('node:123', $mock->tags);
    $this->assertEquals(1, $mock->checkSumResets);
  }

  /**
   * @covers ::__construct
   * @covers ::onReceive
   * @covers ::isValidInstance
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testValidInstances() : void {
    $mock = new CacheTagInvalidator();
    $environmentResolver = $this->getEnvironmentResolver(Project::ASUMINEN, EnvironmentEnum::Local);
    $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    $eventDispatcher->dispatch(Argument::any(), Argument::any())
      ->willReturn(new CacheTagInvalidateEvent());
    $sut = new CacheTagInvalidatorSubscriber($mock, $environmentResolver, $eventDispatcher->reveal());
    $sut->onReceive(new PubSubMessage([
      'data' => [
        'tags' => ['node:123'],
        'instances' => [],
      ],
    ]));
    $this->assertArrayHasKey('node:123', $mock->tags);
    $this->assertEquals(1, $mock->checkSumResets);

    $sut->onReceive(new PubSubMessage([
      'data' => [
        'tags' => ['node:123'],
        'instances' => [Project::ASUMINEN],
      ],
    ]));
    $this->assertArrayHasKey('node:123', $mock->tags);
    $this->assertEquals(2, $mock->checkSumResets);

    // Make sure the message is ignored if projects do not match.
    $sut->onReceive(new PubSubMessage([
      'data' => [
        'tags' => ['node:123'],
        'instances' => [Project::ETUSIVU],
      ],
    ]));
    $this->assertEquals(2, $mock->checkSumResets);
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Cache;

use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface;
use Drupal\helfi_api_base\Cache\CacheTagInvalidator;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use WebSocket\Exception\ClientException;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Cache\CacheTagInvalidator
 * @group helfi_api_base
 */
class CacheTagInvalidatorTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Make sure PubSub message is sent when we invalidate cache tags.
   *
   * @covers ::__construct
   * @covers ::invalidateTags
   */
  public function testCacheTags() : void {
    $client = $this->prophesize(PubSubManagerInterface::class);
    $client->sendMessage(Argument::any())->shouldBeCalledTimes(1);

    $sut = new CacheTagInvalidator($client->reveal());
    $sut->invalidateTags(['node:123']);
  }

  /**
   * Tests that ConnectionExceptions are caught.
   *
   * @covers ::__construct
   * @covers ::invalidateTags
   */
  public function testConnectionException() : void {
    $client = $this->prophesize(PubSubManagerInterface::class);
    $client->sendMessage(Argument::any())
      ->willThrow(ClientException::class)
      ->shouldBeCalledTimes(1);
    $sut = new CacheTagInvalidator($client->reveal());
    $sut->invalidateTags(['node:123']);
  }

}

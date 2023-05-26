<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\Cache;

use Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface;
use Drupal\helfi_api_base\Cache\CacheTagInvalidator;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Cache\CacheTagInvalidator
 * @group helfi_api_base
 */
class CacheInvalidatorSubscriberTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * @covers ::__construct
   * @covers ::invalidateTags
   */
  public function testInvalidCacheTags() : void {
    $client = $this->prophesize(PubSubManagerInterface::class);
    $client->sendMessage(Argument::any())->shouldBeCalledTimes(1);

    $sut = new CacheTagInvalidator($client->reveal());
    $sut->invalidateTags(['node:123']);
  }

}

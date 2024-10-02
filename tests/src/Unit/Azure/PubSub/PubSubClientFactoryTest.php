<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Azure\PubSub\PubSubClientFactory;
use Drupal\helfi_api_base\Azure\PubSub\Settings;
use Prophecy\PhpUnit\ProphecyTrait;
use WebSocket\Client;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Azure\PubSub\PubSubClientFactory
 * @group helfi_api_base
 */
class PubSubClientFactoryTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * @covers ::create
   * @covers \Drupal\helfi_api_base\Azure\PubSub\Settings::__construct
   */
  public function testConstruct() : void {
    $settings = new Settings(
      'hub',
      'group',
      'endpoint',
      'accessToken',
    );
    $sut = new PubSubClientFactory();
    $client = $sut->create($settings, $this->prophesize(TimeInterface::class)->reveal());
    $this->assertInstanceOf(Client::class, $client);
  }

}

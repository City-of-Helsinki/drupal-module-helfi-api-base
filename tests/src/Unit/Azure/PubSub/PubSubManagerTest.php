<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\helfi_api_base\Azure\PubSub\PubSubManager;
use Drupal\helfi_api_base\Azure\PubSub\Settings;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebSocket\Client;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Azure\PubSub\PubSubManager
 * @group helfi_api_base
 */
class PubSubManagerTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * @covers ::sendMessage
   * @covers ::joinGroup
   * @covers ::encodeMessage
   * @covers ::__construct
   * @covers \Drupal\helfi_api_base\Azure\PubSub\Settings::__construct
   */
  public function testSendMessage() : void {
    $time = $this->prophesize(TimeInterface::class);
    $time->getCurrentTime()->willReturn(1234);

    $client = $this->prophesize(Client::class);
    $client->text('{"type":"joinGroup","group":"local"}')->shouldBeCalledTimes(1);
    $client->text('{"type":"sendToGroup","group":"local","dataType":"json","data":{"test":"something","timestamp":1234}}')->shouldBeCalledTimes(2);

    $sut = new PubSubManager(
      $client->reveal(),
      $this->prophesize(EventDispatcherInterface::class)->reveal(),
      $time->reveal(),
      new Settings(
        'hub',
        'local',
        'localhost',
        'token',
      )
    );
    // Send two messages to test that we call ::joinGroup() once.
    $sut->sendMessage(['test' => 'something']);
    $sut->sendMessage(['test' => 'something']);
  }

  /**
   * @covers ::joinGroup
   * @covers ::receive
   * @covers ::encodeMessage
   * @covers ::__construct
   * @covers \Drupal\helfi_api_base\Azure\PubSub\Settings::__construct
   * @covers \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage::__construct
   */
  public function testReceive() : void {
    $expectedMessage = '{"message":"test"}';
    $dispatcher = $this->prophesize(EventDispatcherInterface::class);
    $dispatcher->dispatch(Argument::any())->shouldBeCalledTimes(2);

    $client = $this->prophesize(Client::class);
    $client->text('{"type":"joinGroup","group":"local"}')->shouldBeCalledTimes(1);
    $client->receive()->willReturn($expectedMessage);
    $client->receive()->shouldBeCalledTimes(2);

    $sut = new PubSubManager(
      $client->reveal(),
      $dispatcher->reveal(),
      $this->prophesize(TimeInterface::class)->reveal(),
      new Settings(
        'hub',
        'local',
        'localhost',
        'token',
      )
    );
    // Call twice to make sure we call ::joinGroup() only once.
    $this->assertEquals($expectedMessage, $sut->receive());
    $this->assertEquals($expectedMessage, $sut->receive());
  }

}

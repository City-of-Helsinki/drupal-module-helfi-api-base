<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\helfi_api_base\Azure\PubSub\PubSubClientFactoryInterface;
use Drupal\helfi_api_base\Azure\PubSub\PubSubManager;
use Drupal\helfi_api_base\Azure\PubSub\Settings;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebSocket\Client;
use WebSocket\ConnectionException;

/**
 * Tests PubSub manager service.
 *
 * @group helfi_api_base
 */
class PubSubManagerTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests initializeClient with invalid joinGroup message.
   */
  public function testInitializeClientJoinGroupException() : void {
    $time = $this->prophesize(TimeInterface::class);
    $time->getCurrentTime()->willReturn(1234);

    $client = $this->prophesize(Client::class);
    $client->text('{"type":"joinGroup","group":"local"}')->shouldBeCalledTimes(1);
    $client->receive()->willReturn('');
    $clientFactory = $this->prophesize(PubSubClientFactoryInterface::class);
    $clientFactory->create('token')
      ->willReturn($client->reveal());

    $sut = new PubSubManager(
      $clientFactory->reveal(),
      $this->prophesize(EventDispatcherInterface::class)->reveal(),
      $time->reveal(),
      new Settings(
        'hub',
        'local',
        'localhost',
        ['token'],
      ),
      $this->prophesize(LoggerInterface::class)->reveal(),
    );
    $this->expectException(ConnectionException::class);
    $this->expectExceptionMessage('Failed to initialize the client.');
    $sut->sendMessage(['test' => 'something']);
  }

  /**
   * Tests initializeClient with invalid credentials.
   */
  public function testInitializeClientInvalidCredentials(): void {
    $time = $this->prophesize(TimeInterface::class);
    $time->getCurrentTime()->willReturn(1234);

    $client = $this->prophesize(Client::class);
    $client->text('{"type":"joinGroup","group":"local"}')
      ->shouldBeCalledTimes(1)
      ->willThrow(new ConnectionException('Test exception'));
    $clientFactory = $this->prophesize(PubSubClientFactoryInterface::class);
    $clientFactory->create('token')
      ->willReturn($client->reveal());

    $sut = new PubSubManager(
      $clientFactory->reveal(),
      $this->prophesize(EventDispatcherInterface::class)->reveal(),
      $time->reveal(),
      new Settings(
        'hub',
        'local',
        'localhost',
        ['token'],
      ),
      $this->prophesize(LoggerInterface::class)->reveal(),
    );
    $this->expectException(ConnectionException::class);
    $this->expectExceptionMessage('Test exception');
    $sut->sendMessage(['test' => 'something']);
  }

  /**
   * Tests initializeClient() with empty access keys.
   */
  public function testInitializeClientNoSettings() : void {
    $this->expectException(ConnectionException::class);
    $this->expectExceptionMessage('PubSub access key is undefined.');
    $sut = new PubSubManager(
      $this->prophesize(PubSubClientFactoryInterface::class)->reveal(),
      $this->prophesize(EventDispatcherInterface::class)->reveal(),
      $this->prophesize(TimeInterface::class)->reveal(),
      new Settings('', '', '', []),
      $this->prophesize(LoggerInterface::class)->reveal(),
    );
    // Make sure initialize client fails when settings are empty.
    $sut->sendMessage(['test' => 'something']);
  }

  /**
   * Tests sendMessage() method.
   */
  public function testSendMessage() : void {
    $time = $this->prophesize(TimeInterface::class);
    $time->getCurrentTime()->willReturn(1234);

    $client = $this->prophesize(Client::class);
    $client->receive()->willReturn('{"type":"event","event":"connected"}');
    $client->text('{"type":"joinGroup","group":"local"}')->shouldBeCalledTimes(1);
    $client->text('{"type":"sendToGroup","group":"local","dataType":"json","data":{"test":"something","timestamp":1234}}')->shouldBeCalledTimes(2);
    $clientFactory = $this->prophesize(PubSubClientFactoryInterface::class);
    $clientFactory->create('token')
      ->willReturn($client->reveal());

    $sut = new PubSubManager(
      $clientFactory->reveal(),
      $this->prophesize(EventDispatcherInterface::class)->reveal(),
      $time->reveal(),
      new Settings(
        'hub',
        'local',
        'localhost',
        ['token'],
      ),
      $this->prophesize(LoggerInterface::class)->reveal(),
    );
    // Send two messages to test that we call ::joinGroup() once.
    $sut->sendMessage(['test' => 'something']);
    $sut->sendMessage(['test' => 'something']);
  }

  /**
   * Tests receive() method.
   */
  public function testReceive() : void {
    $expectedMessage = '{"message":"test"}';
    $dispatcher = $this->prophesize(EventDispatcherInterface::class);
    $dispatcher->dispatch(Argument::any())->shouldBeCalledTimes(2);

    $client = $this->prophesize(Client::class);
    $client->text('{"type":"joinGroup","group":"local"}')->shouldBeCalledTimes(1);
    $client->receive()
      ->willReturn(
        '{"type":"event","event":"connected"}',
        $expectedMessage,
      );
    // This called once by ::joinGroup and twice by ::receive().
    $client->receive()->shouldBeCalledTimes(3);
    $clientFactory = $this->prophesize(PubSubClientFactoryInterface::class);
    $clientFactory->create('token')
      ->willReturn($client->reveal());

    $sut = new PubSubManager(
      $clientFactory->reveal(),
      $dispatcher->reveal(),
      $this->prophesize(TimeInterface::class)->reveal(),
      new Settings(
        'hub',
        'local',
        'localhost',
        ['token'],
      ),
      $this->prophesize(LoggerInterface::class)->reveal(),
    );
    // Call twice to make sure we only join group once.
    $this->assertEquals($expectedMessage, $sut->receive());
    $this->assertEquals($expectedMessage, $sut->receive());
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Commands;

use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface;
use Drupal\helfi_api_base\Drush\Commands\PubSubCommands;
use Drush\Commands\DrushCommands;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WebSocket\ConnectionException;
use WebSocket\TimeoutException;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Commands\PubSubCommands
 * @group helfi_api_base
 */
class PubSubCommandsTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests listen.
   */
  public function testListen() : void {
    $expectedMessage = '{"message":"test"}';
    $output = $this->prophesize(OutputInterface::class);
    $input = $this->prophesize(InputInterface::class);
    $io = $this->prophesize(SymfonyStyle::class);
    $io->writeln(Argument::containingString('Received message'))
      ->shouldBeCalledTimes(PubSubCommands::MAX_MESSAGES);
    $io->writeln(Argument::containingString('Received maximum number of messages'))
      ->shouldBeCalledTimes(1);

    $manager = $this->prophesize(PubSubManagerInterface::class);
    $manager->receive()->willReturn($expectedMessage);

    $sut = new PubSubCommands($manager->reveal());
    $sut->restoreState($input->reveal(), $output->reveal(), $io->reveal());
    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $sut->listen());
  }

  /**
   * Tests exception output.
   */
  public function testExceptionOutput() : void {
    $output = $this->prophesize(OutputInterface::class);
    $input = $this->prophesize(InputInterface::class);
    $io = $this->prophesize(SymfonyStyle::class);
    $io->writeln('Invalid json: Syntax error')->shouldBeCalledTimes(PubSubCommands::MAX_MESSAGES);
    $io->writeln(Argument::containingString('Received maximum number of messages'))
      ->shouldBeCalledTimes(1);

    $manager = $this->prophesize(PubSubManagerInterface::class);
    $manager->receive()->willThrow(new \JsonException('Syntax error'));

    $sut = new PubSubCommands($manager->reveal());
    $sut->restoreState($input->reveal(), $output->reveal(), $io->reveal());
    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $sut->listen());
  }

  /**
   * Tests timeout exception.
   */
  public function testTimeoutException() : void {
    $output = $this->prophesize(OutputInterface::class);
    $input = $this->prophesize(InputInterface::class);
    $io = $this->prophesize(SymfonyStyle::class);
    $io->writeln(Argument::containingString('Received maximum number of messages'))
      ->shouldBeCalledTimes(1);

    $manager = $this->prophesize(PubSubManagerInterface::class);
    $manager->receive()->willThrow(TimeoutException::class);

    $sut = new PubSubCommands($manager->reveal());
    $sut->restoreState($input->reveal(), $output->reveal(), $io->reveal());
    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $sut->listen());
  }

  /**
   * Tests connection exception.
   */
  public function testConnectionException() : void {
    $this->expectException(ConnectionException::class);
    $manager = $this->prophesize(PubSubManagerInterface::class);
    $manager->receive()->willThrow(ConnectionException::class);

    $sut = new PubSubCommands($manager->reveal());
    $sut->listen();
  }

}

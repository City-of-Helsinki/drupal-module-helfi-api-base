<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Commands;

use Drupal\helfi_api_base\Commands\DeployCommands;
use Drupal\helfi_api_base\Event\PostDeployEvent;
use Drupal\helfi_api_base\Event\PreDeployEvent;
use Drupal\Tests\UnitTestCase;
use Drush\Commands\DrushCommands;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Commands\DeployCommands
 * @group helfi_api_base
 */
class DeployCommandsTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * @covers ::__construct
   * @covers ::postDeploy
   */
  public function testPostDeploy() : void {
    $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    $eventDispatcher->dispatch(new PostDeployEvent())->shouldBeCalledTimes(1);
    $sut = new DeployCommands($eventDispatcher->reveal());
    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $sut->postDeploy());
  }

  /**
   * @covers ::__construct
   * @covers ::preDeploy
   */
  public function testPreDeploy() : void {
    $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    $eventDispatcher->dispatch(new PreDeployEvent())->shouldBeCalledTimes(1);
    $sut = new DeployCommands($eventDispatcher->reveal());
    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $sut->preDeploy());
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * Tests action logging.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\ActionLog\ActionLogger
 */
class ActionLogTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'entity_test',
    'user',
  ];

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) : void {
    parent::register($container);

    $container->setParameter('helfi_api_base.action_log_entities', [
      'entity_test',
    ]);
  }

  /**
   * Tests that entity operations are logged.
   */
  public function testUserActionLogging(): void {
    $logger = $this->prophesize(LoggerInterface::class);
    $this->container
      ->set('logger.channel.helfi_api_base', $logger->reveal());

    $this->setCurrentUser($this->createUser());

    $logger->info(Argument::containingString("by user @user"), Argument::any())
      ->shouldBeCalledTimes(2);

    $entity = EntityTest::create([]);
    $entity->save();
    $entity->delete();
  }

  /**
   * Tests that API sessions are not logged.
   */
  public function testApiSession(): void {
    $logger = $this->prophesize(LoggerInterface::class);

    // Should not log anything since no user is logged in.
    $logger->info(Argument::any(), Argument::any())
      ->shouldNotBeCalled();

    $this->container
      ->set('logger.channel.helfi_api_base', $logger->reveal());

    $entity = EntityTest::create([]);
    $entity->save();
    $entity->delete();
  }

}

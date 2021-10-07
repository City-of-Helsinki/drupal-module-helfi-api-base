<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\helfi_api_base\EventSubscriber\MigrationSubscriber;
use Drupal\helfi_api_base\EventSubscriber\PartialMigrationSubscriber;
use Drupal\helfi_api_base\MigrateTrait;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;

/**
 * Tests the PartialMigrationSubscriber.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\EventSubscriber\PartialMigrationSubscriber
 * @group helfi_api_base
 */
class PartialMigrationSubscriberTest extends ApiKernelTestBase {

  use MigrateTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'system',
    'remote_entity_test',
  ];

  /**
   * The migration subscriber.
   *
   * @var null|\Drupal\helfi_api_base\EventSubscriber\PartialMigrationSubscriber
   */
  protected ?PartialMigrationSubscriber $partialMigrationSubscriber;

  /**
   * State collection.
   *
   * @var Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->partialMigrationSubscriber = $this->container->get('helfi_api_base.partial_migration_subscriber');
    $this->state = $this->container->get('state');
  }

  /**
   * Gets the migrate import event.
   *
   * @param string $plugin
   *   The plugin.
   *
   * @return \Drupal\migrate\Event\MigrateImportEvent
   *   The migrate import event.
   */
  protected function getMigrationEvent(string $plugin) : MigrateImportEvent {
    // Setup test migration objects.
    $migration_prophecy = $this->prophesize(MigrationInterface::class);
    $migration_prophecy->getSourcePlugin()->willReturn(NULL);
    $migration_prophecy->getDestinationConfiguration()->willReturn(['plugin' => $plugin]);
    $messenger = $this->prophesize(MigrateMessageInterface::class);
    return new MigrateImportEvent($migration_prophecy->reveal(), $messenger->reveal());
  }

  /**
   * Tests that entity state is set correctly.
   */
  public function testState() : void {
    $event = $this->getMigrationEvent('entity:remote_entity_test');
    $this->partialMigrationSubscriber->updateMigrationState($event);
    $this->assertEquals($this->state->get($this->partialMigrationSubscriber::PARTIAL_MIGRATE_KEY), 0);
  }

}

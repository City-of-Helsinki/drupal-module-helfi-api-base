<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\helfi_api_base\EventSubscriber\MigrationSubscriber;
use Drupal\helfi_api_base\MigrateTrait;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;

/**
 * Tests the MigrationSubscriber.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\EventSubscriber\MigrationSubscriber
 * @group helfi_api_base
 */
class MigrationSubscriberTest extends ApiKernelTestBase {

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
   * @var null|\Drupal\helfi_api_base\EventSubscriber\MigrationSubscriber
   */
  protected ?MigrationSubscriber $migrationSubscriber;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('remote_entity_test');
    $this->migrationSubscriber = $this->container->get('helfi_api_base.migration_subscriber');
    $this->setIsPartialMigrate(FALSE);
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
   * Gets the remote entity test entity.
   *
   * @param int $id
   *   The id.
   *
   * @return \Drupal\remote_entity_test\Entity\RemoteEntityTest
   *   The entity.
   */
  protected function getEntity(int $id) : RemoteEntityTest {
    $entity = RemoteEntityTest::create([
      'id' => $id,
      'name' => 'Test ' . $id,
    ]);
    $entity->save();

    return $entity;
  }

  /**
   * Tests that entity is incremented correctly.
   */
  public function testIncrement() : void {
    $entity = $this->getEntity(1);

    $event = $this->getMigrationEvent('entity:remote_entity_test');
    $this->migrationSubscriber->onPreImport($event);

    $entity = $this->reloadEntity($entity);
    $this->assertEquals($entity->getSyncAttempts(), 1);
  }

  /**
   * Make sure invalid entity does nothing.
   *
   * @covers ::onPreImport
   * @covers ::onPostImport
   */
  public function testInvalidEntityType(): void {
    $entity = $this->getEntity(1);

    $event = $this->getMigrationEvent('invalid_entity');
    $this->migrationSubscriber->onPreImport($event);

    $this->assertEquals($entity->getSyncAttempts(), 0);
  }

  /**
   * Tests that entities are deleted correctly.
   */
  public function testAutomaticDeletion() : void {
    $entity = $this->getEntity(1);
    $event = $this->getMigrationEvent('entity:remote_entity_test');

    for ($i = 0; $i < RemoteEntityTest::MAX_SYNC_ATTEMPTS; $i++) {
      $this->migrationSubscriber->onPreImport($event);
    }

    /** @var \Drupal\remote_entity_test\Entity\RemoteEntityTest $entity */
    $entity = $this->reloadEntity($entity);
    $this->assertEquals($entity->getSyncAttempts(), RemoteEntityTest::MAX_SYNC_ATTEMPTS);

    $this->migrationSubscriber->onPostImport($event);

    // Make sure entity was deleted.
    $this->assertEquals(RemoteEntityTest::load(1), NULL);
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel\Plugin\migrate\process;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\helfi_api_base\MigrateTrait;
use Drupal\helfi_api_base\Plugin\migrate\process\EntityChanged;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\MigrateDestinationInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;

/**
 * Tests the entity_changed plugin.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\migrate\process\EntityChanged
 * @group helfi_api_base
 */
class EntityChangedTest extends ApiKernelTestBase {

  use MigrateTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'system',
    'remote_entity_test',
    'entity_changed_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['entity_changed_test']);
    $this->installEntitySchema('remote_entity_test');
  }

  /**
   * Gets the 'entity_has_changed' migrate process plugin.
   *
   * @return \Drupal\helfi_api_base\Plugin\migrate\process\EntityChanged
   *   The entity has changed migrate process plugin.
   */
  protected function getSut() : EntityChanged {
    // Setup test migration objects.
    $migration_prophecy = $this->prophesize(MigrationInterface::class);
    $migrate_destination_prophecy = $this->prophesize(MigrateDestinationInterface::class);
    $migrate_destination_prophecy->getPluginId()->willReturn('remote_entity_test');
    $migrate_destination = $migrate_destination_prophecy->reveal();
    $migration_prophecy->getDestinationPlugin()->willReturn($migrate_destination);
    $migration_prophecy->getProcess()->willReturn([]);
    $migration = $migration_prophecy->reveal();
    $configuration = [
      'entity_type' => 'remote_entity_test',
    ];
    return \Drupal::service('plugin.manager.migrate.process')
      ->createInstance('entity_has_changed', $configuration, $migration);
  }

  /**
   * Asserts that the migrate row is equal to given entity value.
   *
   * @param int $id
   *   The entity id.
   * @param string $entity_date
   *   The entity date.
   * @param string $row_date
   *   The row date.
   */
  private function assertTransform(int $id, string $entity_date, string $row_date) : void {
    $executable = $this->prophesize(MigrateExecutableInterface::class)->reveal();

    $entityDate = DateTimePlus::createFromFormat('Y-m-d\TH:i:s', $entity_date)
      ->format('U');
    $entity = RemoteEntityTest::create([
      'id' => $id,
      'title' => 'Test title',
      'changed' => $entityDate,
    ]);
    $entity->save();

    $row = new Row();
    $row->setIdMap(['sourceid1' => $entity->id()]);

    $rowDate = DateTimePlus::createFromFormat('Y-m-d\TH:i:s', $row_date)->format('U');
    $value = $this->getSut()->transform($rowDate, $executable, $row, 'changed');

    $this->assertEqual($rowDate, $value);
  }

  /**
   * The data provider for entity not changed tests.
   *
   * @return array[]
   *   The data.
   */
  public function entityNotChangedData() {
    return [
      [1, '2020-10-29T10:05:05', '2020-10-29T10:05:05'],
      [2, '2020-10-29T09:05:05', '2020-10-28T10:05:05'],
    ];
  }

  /**
   * Tests entity when it has not changed.
   *
   * @covers ::transform
   * @dataProvider entityNotChangedData
   */
  public function testEntityNotChanged(int $id, string $date1, string $date2): void {
    $this->setIsPartialMigrate(TRUE);

    $this->expectException(MigrateSkipRowException::class);
    $this->assertTransform($id, $date1, $date2);
  }

  /**
   * Tests entity when it has not changed.
   *
   * @covers ::transform
   * @dataProvider entityNotChangedData
   */
  public function testEntityNotChangedNotPartialMigrate(int $id, string $date1, string $date2): void {
    // Test that we just pass the field value through when
    // we're not doing a partial migrate.
    $this->setIsPartialMigrate(FALSE);
    $this->assertTransform($id, $date1, $date2);
  }

  /**
   * Tests entity when it has changed.
   */
  public function testEntityHasChanged() : void {
    $this->assertTransform(1, '2020-10-28T10:00:01', '2020-10-30T12:00:00');
  }

}

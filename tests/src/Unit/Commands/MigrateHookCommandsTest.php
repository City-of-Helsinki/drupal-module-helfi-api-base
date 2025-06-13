<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Drush\Commands\MigrateHookCommands;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Robo\ResultData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Drush\Commands\MigrateHookCommands
 * @group helfi_api_base
 */
class MigrateHookCommandsTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * @covers ::__construct
   * @covers ::addMigrateHookOptions
   */
  public function testAddMigrateHookOptions() : void {
    $sut = new MigrateHookCommands(
      $this->prophesize(MigrationPluginManagerInterface::class)->reveal(),
      $this->prophesize(KeyValueFactoryInterface::class)->reveal(),
      $this->prophesize(TimeInterface::class)->reveal(),
    );
    $command = new Command();
    $sut->addMigrateHookOptions($command);
    $this->assertTrue($command->getDefinition()->hasOption('interval'));
    $this->assertTrue($command->getDefinition()->hasOption('reset-threshold'));
  }

  /**
   * @covers ::skipMigrationsHook
   * @covers ::resetMigrationsHook
   * @cover ::__construct
   */
  public function testOptionsNotSet() : void {
    $input = $this->prophesize(InputInterface::class);
    $output = $this->prophesize(OutputInterface::class);
    $commandData = new CommandData(new AnnotationData(), $input->reveal(), $output->reveal());

    $sut = new MigrateHookCommands(
      $this->prophesize(MigrationPluginManagerInterface::class)->reveal(),
      $this->prophesize(KeyValueFactoryInterface::class)->reveal(),
      $this->prophesize(TimeInterface::class)->reveal(),
    );
    $this->assertNull($sut->skipMigrationsHook($commandData));
    $this->assertNull($sut->resetMigrationsHook($commandData));
  }

  /**
   * @covers ::skipMigrationsHook
   * @covers ::resetMigrationsHook
   * @covers ::getMigrations
   * @cover ::__construct
   */
  public function testMigrationsNotFound() : void {
    $input = $this->prophesize(InputInterface::class);
    $input->getOption('interval')->willReturn(10);
    $input->getOption('reset-threshold')->willReturn(10);
    $input->getArgument('migrationIds')->willReturn(NULL);
    $output = $this->prophesize(OutputInterface::class);
    $commandData = new CommandData(new AnnotationData(), $input->reveal(), $output->reveal());

    $sut = new MigrateHookCommands(
      $this->prophesize(MigrationPluginManagerInterface::class)->reveal(),
      $this->prophesize(KeyValueFactoryInterface::class)->reveal(),
      $this->prophesize(TimeInterface::class)->reveal(),
    );
    $this->assertNull($sut->skipMigrationsHook($commandData));
    $this->assertNull($sut->resetMigrationsHook($commandData));
  }

  /**
   * @covers ::skipMigrationsHook
   * @cover ::__construct
   * @covers ::getMigrations
   */
  public function testNoSkippedMigrations() : void {
    $input = $this->prophesize(InputInterface::class);
    $input->getArgument('migrationIds')
      ->willReturn('tpr_unit,tpr_service');
    $input->getOption('interval')->willReturn(NULL);

    $migrationManager = $this->prophesize(MigrationPluginManagerInterface::class);
    $migrationManager->createInstances(['tpr_unit', 'tpr_service'])
      ->willReturn([
        $this->prophesize(MigrationInterface::class)->reveal(),
        $this->prophesize(MigrationInterface::class)->reveal(),
      ]);

    $output = $this->prophesize(OutputInterface::class);
    $commandData = new CommandData(new AnnotationData(), $input->reveal(), $output->reveal());

    $sut = new MigrateHookCommands(
      $migrationManager->reveal(),
      $this->prophesize(KeyValueFactoryInterface::class)->reveal(),
      $this->prophesize(TimeInterface::class)->reveal(),
    );
    $this->assertNull($sut->skipMigrationsHook($commandData));
  }

  /**
   * @covers ::skipMigrationsHook
   * @covers ::migrationIntervalExceeded
   * @covers ::getLastImported
   * @cover ::__construct
   * @covers ::getMigrations
   */
  public function testSkipNoMigrationSkipped() : void {
    $input = $this->prophesize(InputInterface::class);
    $input->getArgument('migrationIds')
      ->willReturn('tpr_unit');
    $input->getOption('interval')->willReturn(43200);

    $migration = $this->prophesize(MigrationInterface::class);
    $migration->id()->willReturn('tpr_unit');

    $migrationManager = $this->prophesize(MigrationPluginManagerInterface::class);
    $migrationManager->createInstances(['tpr_unit'])
      ->willReturn([
        $migration->reveal(),
      ]);

    $output = $this->prophesize(OutputInterface::class);
    $commandData = new CommandData(new AnnotationData(), $input->reveal(), $output->reveal());

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore->get(Argument::any(), Argument::any())->willReturn(NULL);

    $keyValue = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValue->get('migrate_last_imported')->willReturn($keyValueStore->reveal());

    $time = $this->prophesize(TimeInterface::class);
    $time->getCurrentTime()->willReturn(1234567);

    $sut = new MigrateHookCommands(
      $migrationManager->reveal(),
      $keyValue->reveal(),
      $time->reveal(),
    );
    // Make sure no migration is skipped if the interval has not
    // exceeded.
    $this->assertNull($sut->skipMigrationsHook($commandData));
  }

  /**
   * @covers ::skipMigrationsHook
   * @covers ::migrationIntervalExceeded
   * @covers ::getLastImported
   * @covers ::__construct
   * @covers ::createResult
   * @covers ::getMigrations
   */
  public function testMigrationSkipped() : void {
    $input = $this->prophesize(InputInterface::class);
    $input->getArgument('migrationIds')
      ->willReturn('tpr_unit');
    $input->getOption('interval')->willReturn(10);

    $migration = $this->prophesize(MigrationInterface::class);
    $migration->id()->willReturn('tpr_unit');

    $migrationManager = $this->prophesize(MigrationPluginManagerInterface::class);
    $migrationManager->createInstances(['tpr_unit'])
      ->willReturn([
        $migration->reveal(),
      ]);

    $output = $this->prophesize(OutputInterface::class);
    $commandData = new CommandData(new AnnotationData(), $input->reveal(), $output->reveal());

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    // Migrate last imported returns time in microseconds.
    $keyValueStore->get(Argument::any(), Argument::any())->willReturn(100 * 1000);

    $keyValue = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValue->get('migrate_last_imported')->willReturn($keyValueStore->reveal());

    $time = $this->prophesize(TimeInterface::class);
    $time->getCurrentTime()->willReturn(105);

    $sut = new MigrateHookCommands(
      $migrationManager->reveal(),
      $keyValue->reveal(),
      $time->reveal(),
    );
    // Make sure migration is skipped since the interval is configured to be 10
    // seconds, the migration was last run at 100, and the current time is 105.
    $result = $sut->skipMigrationsHook($commandData);
    $this->assertInstanceOf(ResultData::class, $result);
    $this->assertMatchesRegularExpression('/tpr_unit: Migration has been/', $result->getMessage());
  }

  /**
   * @covers ::resetMigrationsHook
   * @covers ::migrationIntervalExceeded
   * @covers ::getLastImported
   * @covers ::getMigrations
   * @cover ::__construct
   */
  public function testMigrationResetThresholdNotExceeded() : void {
    $input = $this->prophesize(InputInterface::class);
    $input->getArgument('migrationIds')
      ->willReturn('tpr_unit');
    $input->getOption('reset-threshold')->willReturn(10);

    $migration = $this->prophesize(MigrationInterface::class);
    $migration->id()->willReturn('tpr_unit');
    $migration->getStatus()
      ->shouldBeCalled()
      ->willReturn(MigrationInterface::STATUS_IMPORTING);
    $migration->setStatus(MigrationInterface::STATUS_IDLE)->shouldNotBeCalled();

    $migrationManager = $this->prophesize(MigrationPluginManagerInterface::class);
    $migrationManager->createInstances(['tpr_unit'])
      ->willReturn([
        $migration->reveal(),
      ]);

    $output = $this->prophesize(OutputInterface::class);
    $commandData = new CommandData(new AnnotationData(), $input->reveal(), $output->reveal());

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    // Migrate last imported returns time in microseconds.
    $keyValueStore->get(Argument::any(), Argument::any())->willReturn(100 * 1000);

    $keyValue = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValue->get('migrate_last_imported')->willReturn($keyValueStore->reveal());

    $time = $this->prophesize(TimeInterface::class);
    $time->getCurrentTime()->willReturn(105);

    $sut = new MigrateHookCommands(
      $migrationManager->reveal(),
      $keyValue->reveal(),
      $time->reveal(),
    );
    $result = $sut->resetMigrationsHook($commandData);
    $this->assertNull($result);
  }

  /**
   * @covers ::resetMigrationsHook
   * @covers ::migrationIntervalExceeded
   * @covers ::getLastImported
   * @covers ::__construct
   * @covers ::getMigrations
   * @covers ::createResult
   */
  public function testMigrationReset() : void {
    $input = $this->prophesize(InputInterface::class);
    $input->getArgument('migrationIds')
      ->willReturn('tpr_unit,tpr_service');
    $input->getOption('reset-threshold')->willReturn(10);

    $migration = $this->prophesize(MigrationInterface::class);
    $migration->id()->willReturn('tpr_unit');
    $migration->getStatus()
      ->shouldBeCalled()
      ->willReturn(MigrationInterface::STATUS_IMPORTING);
    $migration2 = $this->prophesize(MigrationInterface::class);
    $migration2->id()->willReturn('tpr_service');
    $migration2->getStatus()->willReturn(MigrationInterface::STATUS_IDLE);
    // Make sure migration is set back to idle.
    $migration->setStatus(MigrationInterface::STATUS_IDLE)->shouldBeCalled();

    $migrationManager = $this->prophesize(MigrationPluginManagerInterface::class);
    $migrationManager->createInstances(['tpr_unit', 'tpr_service'])
      ->shouldBeCalled()
      ->willReturn([
        $migration->reveal(),
        $migration2->reveal(),
      ]);

    $output = $this->prophesize(OutputInterface::class);
    $commandData = new CommandData(new AnnotationData(), $input->reveal(), $output->reveal());

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    // Migrate last imported returns time in microseconds.
    $keyValueStore->get(Argument::any(), Argument::any())->willReturn(100 * 1000);

    $keyValue = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValue->get('migrate_last_imported')->willReturn($keyValueStore->reveal());

    $time = $this->prophesize(TimeInterface::class);
    $time->getCurrentTime()->willReturn(115);

    $sut = new MigrateHookCommands(
      $migrationManager->reveal(),
      $keyValue->reveal(),
      $time->reveal(),
    );
    // Make sure migration is reset back to idle since the migration was last
    // run at 100, the migration reset-threshold is configured to 10 seconds,
    // and the current time is 115.
    $result = $sut->resetMigrationsHook($commandData);
    $this->assertInstanceOf(ResultData::class, $result);
    $this->assertMatchesRegularExpression('/tpr_unit: Migration status was reset back to idle/', $result->getMessage());
  }

}

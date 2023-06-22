<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drush\Attributes\Hook;
use Drush\Commands\DrushCommands;
use Drush\Utils\StringUtils;
use Robo\ResultData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Migrate hook commands.
 */
final class MigrateHookCommands extends DrushCommands {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migrationPluginManager
   *   The migration plugin manager.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValueFactory
   *   The key value service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    private readonly MigrationPluginManagerInterface $migrationPluginManager,
    private readonly KeyValueFactoryInterface $keyValueFactory,
    private readonly TimeInterface $time,
  ) {
  }

  /**
   * Gets the last imported timestamp of a migration.
   *
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration.
   *
   * @return int
   *   The last imported time.
   */
  private function getLastImported(MigrationInterface $migration) : int {
    $lastImported = $this->keyValueFactory->get('migrate_last_imported')?->get($migration->id(), 0);
    return (int) round($lastImported / 1000);
  }

  /**
   * Checks if a migration interval has been exceeded or not.
   *
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration.
   * @param int $seconds
   *   The interval time.
   *
   * @return bool
   *   TRUE if the interval has been exceeded.
   */
  private function migrationIntervalExceeded(MigrationInterface $migration, int $seconds) : bool {
    $intervalTime = $this->getLastImported($migration) + $seconds;
    $currentTime = $this->time->getCurrentTime();

    if ($currentTime > $intervalTime) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Adds 'interval' and 'reset-threshold' options to migrate:import command.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   *   The command.
   */
  #[Hook(type: HookManager::OPTION_HOOK, target: 'migrate:import')]
  public function addMigrateHookOptions(Command $command) : void {
    $command->addOption(
      'interval',
      mode: InputOption::VALUE_OPTIONAL,
      description: 'An integer value to determine how often the migration can be run',
    );
    $command->addOption(
      'reset-threshold',
      mode: InputOption::VALUE_OPTIONAL,
      description: 'An integer value to determine when a stuck migration should be reset',
    );
  }

  /**
   * Constructs the migration plugins for given IDs.
   *
   * @param string $ids
   *   The migration ids.
   *
   * @return \Drupal\migrate\Plugin\MigrationInterface[]
   *   The migrations.
   */
  private function getMigrations(string $ids) : array {
    $migrationIds = StringUtils::csvToArray($ids);
    $migrations = $this->migrationPluginManager->createInstances($migrationIds);

    return $migrations ?? [];
  }

  /**
   * Constructs a message for given migrations.
   *
   * @param array $migrationIds
   *   The migration ids.
   * @param string $message
   *   The message.
   *
   * @return \Robo\ResultData
   *   The result.
   */
  private function createResult(array $migrationIds, string $message) : ResultData {
    $messages = [];

    foreach ($migrationIds as $id) {
      $messages[] = sprintf('<comment>[%s] %s: %s</comment>', self::class, $id, $message);
    }
    return new ResultData(message: implode(PHP_EOL, $messages));
  }

  /**
   * Checks if the migration status should be reset.
   *
   * Reset migration status to 'idle' if the migration has been running for
   * longer than the value defined in 'reset-threshold' option.
   *
   * For example, call 'drush migrate:import tpr_service --reset-threshold 3600'
   * to reset migration if it has been running for longer than 1 hour.
   *
   * @param \Consolidation\AnnotatedCommand\CommandData $commandData
   *   The command data.
   *
   * @return \Robo\ResultData|null
   *   The result or null.
   */
  #[Hook(type: HookManager::PRE_ARGUMENT_VALIDATOR, target: 'migrate:import')]
  public function resetMigrationsHook(CommandData $commandData) : ?ResultData {
    $threshold = (int) $commandData->input()->getOption('reset-threshold') ?: NULL;

    if (!$threshold) {
      return NULL;
    }
    $migrations = $this->getMigrations((string) $commandData->input()->getArgument('migrationIds'));

    if (!$migrations) {
      return NULL;
    }

    $resetMigrations = [];

    foreach ($migrations as $migration) {
      if ($migration->getStatus() === MigrationInterface::STATUS_IDLE) {
        continue;
      }

      if ($this->migrationIntervalExceeded($migration, $threshold)) {
        $resetMigrations[] = $migration->id();

        // Reset migration status if it has been running for longer than the
        // configured maximum.
        $migration->setStatus(MigrationInterface::STATUS_IDLE);
      }
    }

    if (!$resetMigrations) {
      return NULL;
    }

    $result = $this->createResult($resetMigrations, 'Migration status was reset back to idle.');
    $commandData->output()
      ->writeln($result->getMessage());

    return $result;
  }

  /**
   * Checks if migrations should be skipped.
   *
   * Skip the migration if it has been less than N seconds since the last
   * run. This can be configured by passing 'interval' option. For example,
   * call 'drush migrate:import tpr_service --interval 3600' to allow migration
   * to be run once an hour.
   *
   * @return \Robo\ResultData|null
   *   The result or null.
   */
  #[Hook(type: HookManager::PRE_ARGUMENT_VALIDATOR, target: 'migrate:import')]
  public function skipMigrationsHook(CommandData $commandData) : ?ResultData {
    $interval = (int) $commandData->input()->getOption('interval') ?: NULL;

    if (!$interval) {
      return NULL;
    }
    $migrations = $this->getMigrations((string) $commandData->input()->getArgument('migrationIds'));

    if (!$migrations) {
      return NULL;
    }
    $skippedMigrations = [];

    foreach ($migrations as $migration) {
      if (!$this->migrationIntervalExceeded($migration, $interval)) {
        $skippedMigrations[] = $migration->id();
      }
    }

    if (!$skippedMigrations) {
      return NULL;
    }
    $result = $this->createResult($skippedMigrations, sprintf('Migration has been run in the past %d seconds. Skipping ...', $interval));
    $commandData->output()->writeln($result->getMessage());

    return $result;
  }

}

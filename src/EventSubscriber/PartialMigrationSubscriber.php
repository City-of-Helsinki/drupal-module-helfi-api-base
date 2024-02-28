<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resolve the state of the migration.
 */
final class PartialMigrationSubscriber implements EventSubscriberInterface {

  /**
   * The key for migration state saved in key_value store.
   *
   * @var string
   */
  public const PARTIAL_MIGRATE_KEY = 'partial_migrate_state';

  /**
   * The key for last full migration timestamp saved in key_value store.
   *
   * @var string
   */
  public const PARTIAL_MIGRATE_LAST_FULL_KEY = 'partial_migrate_previous_full';

  /**
   * Interval for running full migrations.
   *
   * @var int
   */
  public const PARTIAL_MIGRATE_INTERVAL = 604800;

  /**
   * State collection.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   Key value factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   */
  public function __construct(StateInterface $state, TimeInterface $time) {
    $this->state = $state;
    $this->time = $time;
  }

  /**
   * Resolve whether to run full or partial TPR migration as next migration.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The migrate event.
   */
  public function updateMigrationState(MigrateImportEvent $event): void {
    $migrationStateKey = self::PARTIAL_MIGRATE_KEY . '_' . $event->getMigration()->id();
    $migrationLastFullTimeKey = self::PARTIAL_MIGRATE_LAST_FULL_KEY . '_' . $event->getMigration()->id();

    $lastFullMigrate = $this->state->get($migrationLastFullTimeKey);
    if (is_null($lastFullMigrate)) {
      $this->scheduleFullMigration($migrationStateKey, $migrationLastFullTimeKey);
      return;
    }

    $timeDifference = $this->time->getCurrentTime() - (int) $lastFullMigrate;
    if ($timeDifference == 0 || $timeDifference > self::PARTIAL_MIGRATE_INTERVAL) {
      $this->scheduleFullMigration($migrationStateKey, $migrationLastFullTimeKey);
      return;
    }
    $this->schedulePartialMigration($migrationStateKey);
  }

  /**
   * Set the next migration to be a full migration.
   *
   * @param string $migrationStateKey
   *   State to update.
   * @param string $migrationLastFullTimeKey
   *   The "last run" -state to update.
   */
  private function scheduleFullMigration(string $migrationStateKey, string $migrationLastFullTimeKey): void {
    $this->state->set($migrationStateKey, 0);
    $this->state->set($migrationLastFullTimeKey, $this->time->getCurrentTime());
  }

  /**
   * Set the next migration to be a partial migration.
   *
   * @param string $migrationStateKey
   *   State to update.
   */
  private function schedulePartialMigration(string $migrationStateKey): void {
    $this->state->set($migrationStateKey, 1);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'migrate.post_import' => ['updateMigrationState'],
    ];
  }

}

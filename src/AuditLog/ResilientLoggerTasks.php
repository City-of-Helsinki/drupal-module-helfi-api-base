<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\AuditLog;

use Drupal\Core\Lock\LockBackendInterface;
use ResilientLogger\ResilientLogger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Drupal-specific helper for scheduling ResilientLogger tasks.
 *
 * Tasks are not executed immediately but are evaluated during Drupal's cron
 * runs. Each task runs conditionally based on time offsets and the last
 * execution time, not on every cron trigger.
 *
 * @see https://www.php.net/manual/en/function.strtotime.php
 *
 * Parameter block "resilient_logger.tasks" is used to read values for
 * "offset_submit" and "offset_clear". If these are not found, defaults
 * will be used instead.
 */
readonly class ResilientLoggerTasks {

  private const string UNSENT_ENTRIES_LOCK = 'resilient_logger_unsent_entries_lock';
  private const string CLEAR_SENT_ENTRIES_LOCK = 'resilient_logger_clear_sent_entries_lock';

  /**
   * Acquire lock for 30 minutes.
   */
  private const float LOCK_TIME = 1800;

  public function __construct(
    #[Autowire(service: 'lock')] private LockBackendInterface $lock,
    private ResilientLogger $service,
  ) {
  }

  /**
   * Handle cron task.
   *
   * @see \Drupal\helfi_api_base\Hook\AuditLogCronHooks::cron()
   */
  public function handleTasks(int $currentTime): void {
    $this->handleSubmitUnsentEntries($currentTime);
    $this->handleClearSentEntries($currentTime);
  }

  /**
   * Submits audit log events from the database.
   */
  public function handleSubmitUnsentEntries(int $currentTime): void {
    if ($this->lock->acquire(self::UNSENT_ENTRIES_LOCK, self::LOCK_TIME)) {
      $this->service->submitUnsentEntries();

      $this->lock->release(self::UNSENT_ENTRIES_LOCK);
    }
  }

  /**
   * Clears sent audit log events from the database.
   */
  public function handleClearSentEntries(int $currentTime): void {
    if ($this->lock->acquire(self::CLEAR_SENT_ENTRIES_LOCK, self::LOCK_TIME)) {
      $this->service->clearSentEntries();

      $this->lock->release(self::CLEAR_SENT_ENTRIES_LOCK);
    }
  }

}

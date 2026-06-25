<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\AuditLog;

use Psr\Log\LoggerInterface;
use ResilientLogger\ResilientLogger;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Site\Settings;
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
class ResilientLoggerTasks {
  private const string SETTINGS_NAME = "resilient_logger";

  private const string DEFAULT_OFFSET_SUBMIT = "+15min";
  private const string DEFAULT_OFFSET_CLEAR = "first day of next month midnight";

  private const string PARAM_KEY_OFFSET_SUBMIT = "schedule_submit_unsent_entries";
  private const string PARAM_KEY_OFFSET_CLEAR = "schedule_clear_sent_entries";

  private const string STATE_KEY_PREV_SUBMIT = "resilient_logger.prev_submit_unsent";
  private const string STATE_KEY_PREV_CLEAR = "resilient_logger.prev_clear_sent";

  /**
   * String representation of the next time submit unsent entries should run.
   *
   * Defaults to +15min from previous one.
   */
  private string $submitDateOffset;

  /**
   * String representation of the next time clear old entries should run.
   *
   * Defaults to first day of the next month at 00:00.
   */
  private string $clearDateOffset;

  public function __construct(
    private readonly StateInterface $state,
    private readonly ResilientLogger $service,
    #[Autowire(service: 'logger.channel.helfi_api_base')]
    private readonly LoggerInterface $logger,
    Settings $settings,
  ) {
    // Retrieve your resilient_logger settings.
    $config = $settings->get(self::SETTINGS_NAME, []);

    $this->submitDateOffset = $config[self::PARAM_KEY_OFFSET_SUBMIT] ?? self::DEFAULT_OFFSET_SUBMIT;
    $this->clearDateOffset = $config[self::PARAM_KEY_OFFSET_CLEAR] ?? self::DEFAULT_OFFSET_CLEAR;
  }

  /**
   * Handle cron task.
   *
   * @see \Drupal\helfi_api_base\Hook\CronHook::cron()
   */
  public function handleTasks(int $currentTime): void {
    $this->handleSubmitUnsentEntries($currentTime);
    $this->handleClearSentEntries($currentTime);
  }

  /**
   * Submits audit log events from the database.
   */
  public function handleSubmitUnsentEntries(int $currentTime): void {
    $shouldSubmitUnsent = $this->isTaskDue(
      self::STATE_KEY_PREV_SUBMIT,
      $this->submitDateOffset,
      $currentTime
    );

    if ($shouldSubmitUnsent) {
      $this->logger->info("Submitting unsent entries");
      $this->service->submitUnsentEntries();
      $this->state->set(self::STATE_KEY_PREV_SUBMIT, $currentTime);
    }
  }

  /**
   * Clears sent audit log events from the database.
   */
  public function handleClearSentEntries(int $currentTime): void {
    $shouldClearSent = $this->isTaskDue(
      self::STATE_KEY_PREV_CLEAR,
      $this->clearDateOffset,
      $currentTime
    );

    if ($shouldClearSent) {
      $this->logger->info("Clearing sent entries");
      $this->service->clearSentEntries();
      $this->state->set(self::STATE_KEY_PREV_CLEAR, $currentTime);
    }
  }

  /**
   * Checks if the task should run.
   */
  public function isTaskDue(
    string $stateKey,
    string $dateOffset,
    int $currentTime,
  ): bool {
    if ($dateOffset == NULL) {
      return FALSE;
    }

    $prevTriggerAt = $this->state->get($stateKey, 0);
    $nextTriggerAt = strtotime($dateOffset, $prevTriggerAt);

    if ($nextTriggerAt === FALSE) {
      return FALSE;
    }

    return $nextTriggerAt < $currentTime;
  }

}

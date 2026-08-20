<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Hook;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\helfi_api_base\AuditLog\ResilientLoggerTasks;

/**
 * Resilient logger cron tasks.
 */
final readonly class AuditLogCronHooks {

  public function __construct(
    private TimeInterface $time,
    // The ResilientLogger tasks are an optional dependency: the service is only
    // registered when the audit log has been configured via the
    // 'resilient_logger' setting.
    // @see \Drupal\helfi_api_base\HelfiApiBaseServiceProvider::register()
    private ?ResilientLoggerTasks $resilientLoggerTasks = NULL,
  ) {
  }

  /**
   * Implements hook_cron().
   */
  #[Hook('cron')]
  public function cron(): void {
    if (!$this->resilientLoggerTasks) {
      return;
    }
    // Evaluate and run scheduled audit log ResilientLogger tasks.
    $this->resilientLoggerTasks->handleTasks($this->time->getCurrentTime());
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\helfi_api_base\AuditLog\ResilientLoggerTasks;
use Drupal\helfi_api_base\Features\FeatureManager;
use Drupal\helfi_api_base\UserExpire\UserExpireManager;

/**
 * Implements hook_cron().
 */
final readonly class CronHook {

  public function __construct(
    private FeatureManager $featureManager,
    private UserExpireManager $userExpireManager,
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
    if ($this->featureManager->isEnabled(FeatureManager::USER_EXPIRE)) {
      $this->userExpireManager->cancelExpiredUsers();
    }

    if ($this->featureManager->isEnabled(FeatureManager::USER_DELETE)) {
      $this->userExpireManager->deleteExpiredUsers();
    }

    // Evaluate and run scheduled audit log ResilientLogger tasks.
    $this->resilientLoggerTasks?->handleTasks(time());
  }

}

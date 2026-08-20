<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\helfi_api_base\Features\FeatureManager;
use Drupal\helfi_api_base\UserExpire\UserExpireManager;

/**
 * Implements hook_cron().
 */
final readonly class UserExpireCronHooks {

  public function __construct(
    private FeatureManager $featureManager,
    private UserExpireManager $userExpireManager,
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
  }

}

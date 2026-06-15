<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;

/**
 * Audit log hooks.
 */
final readonly class AuditLogHooks {

  public function __construct(
    private AuditLogServiceInterface $auditLogService,
  ) {
  }

  /**
   * Implements hook_user_login().
   */
  #[Hook('user_login')]
  public function onUserLogin(AccountInterface $account): void {
    $this->auditLogService->logOperation(new AuditLogEvent(
      operation: 'DRUPAL_LOGIN',
      message: sprintf('User "%s" (UID: %d) logged in', $account->getAccountName(), $account->id()),
      target: [
        'id' => $account->id(),
        'type' => 'USER',
        'name' => $account->getAccountName(),
      ],
    ));
  }

  /**
   * Implements hook_user_logout().
   */
  #[Hook('user_logout')]
  public function onUserLogout(AccountInterface $account): void {
    $this->auditLogService->logOperation(new AuditLogEvent(
      operation: 'DRUPAL_LOGOUT',
      message: sprintf('User "%s" (UID: %d) logged out', $account->getAccountName(), $account->id()),
      target: [
        'id' => $account->id(),
        'type' => 'USER',
        'name' => $account->getAccountName(),
      ],
    ));
  }

}

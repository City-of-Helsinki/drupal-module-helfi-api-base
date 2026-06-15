<?php

declare (strict_types=1);

namespace Drupal\helfi_api_base\AuditLog;

use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;

/**
 * Interface for AuditLogServices.
 */
interface AuditLogServiceInterface {

  /**
   * Dispatch AuditLogEvent and write the resulting message to the database.
   *
   * @param \Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent $event
   *   The audit log event to log.
   */
  public function logOperation(AuditLogEvent $event): void;

}

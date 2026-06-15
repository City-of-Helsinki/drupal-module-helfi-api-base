<?php

namespace Drupal\helfi_api_base\AuditLog;

/**
 * Interface for AuditLogServices.
 */
interface AuditLogServiceInterface {

  /**
   * Operation that logs the message to database.
   *
   * @param array $message
   *   Message that is merged with generic data and logged to database.
   * @param string $origin
   *   String identifying the source for the audit log message.
   */
  public function logOperation(array $message, string $origin): void;

  /**
   * Dispatch AuditLogEvent.
   *
   * @param array $message
   *   Message associated with the event.
   */
  public function dispatchEvent(array $message): void;

}

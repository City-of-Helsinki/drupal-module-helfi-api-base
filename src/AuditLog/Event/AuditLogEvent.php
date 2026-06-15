<?php

declare (strict_types=1);

namespace Drupal\helfi_api_base\AuditLog\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event class for audit log use.
 *
 * This event allows other modules to control what will
 * will be written in the audit log or invalidate the event
 * to prevent it from ending up in the log.
 *
 * @see \Drupal\helfi_api_base\AuditLog\EventSubscriber\AuditLogEventSubscriber
 */
class AuditLogEvent extends Event {

  /**
   * The name of the audit log events.
   */
  const string LOG = 'helfi_api_base.audit_log_event';

  /**
   * Construct a new event object.
   *
   * @param array<string, mixed> $message
   *   Message associated with the event.
   * @param string $origin
   *   String identifying the source for the audit log message.
   */
  public function __construct(
    readonly public array $message,
    readonly public string $origin = 'DRUPAL',
  ) {
  }

  /**
   * Get message data.
   *
   * @return array<string, mixed>
   *   Message associated with the event.
   */
  public function getMessage(): array {
    return $this->message;
  }

  /**
   * Get origin.
   */
  public function getOrigin(): string {
    return $this->origin;
  }

}

<?php

declare (strict_types=1);

namespace Drupal\helfi_api_base\AuditLog\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event class for audit log use.
 *
 * Event subscribers may enrich the event before it is written
 * to the audit log. Origin and environment fields are injected
 * in AuditLogSourceEntry.
 *
 * @see https://helsinkisolutionoffice.atlassian.net/wiki/spaces/platta/pages/10189438980/Implementing+audit+logging
 * @see \Drupal\helfi_api_base\AuditLog\Sources\AuditLogSourceEntry::getDocument()
 * @see \Drupal\helfi_api_base\AuditLog\AuditLogService::logOperation()
 */
class AuditLogEvent extends Event {

  /**
   * Construct a new event object.
   *
   * @param string $operation
   *   The type of action performed (e.g., create, update, delete).
   * @param string $message
   *   Descriptive information about the action.
   * @param array<string, mixed> $target
   *   The target of the operation.
   * @param array<string, mixed> $extra
   *   Optional additional structured information.
   * @param array<string, mixed> $actor
   *   Who performed the action (user or system).
   * @param \DateTimeImmutable $dateTime
   *   When the action occurred. Defaults to the current UTC time.
   */
  public function __construct(
    public readonly string $operation,
    public readonly string $message,
    public readonly array $target,
    public readonly array $extra = [],
    protected array $actor = [],
    public readonly \DateTimeImmutable $dateTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
  ) {
  }

  /**
   * Set the acting user.
   *
   * @param array<string, mixed> $actor
   *   The actor associated with the event.
   */
  public function setActor(array $actor): self {
    $this->actor = $actor;
    return $this;
  }

  /**
   * Get the acting user.
   *
   * @phpstan-return array<string, mixed>
   */
  public function getActor(): array {
    return $this->actor;
  }

  /**
   * Get the audit event data.
   *
   * @return array<string, mixed>
   *   The data associated with the event.
   */
  public function getData(): array {
    $data = [
      // Format should be yyyy-MM-ddThh:mm:ss.SSSZ.
      'date_time' => $this->dateTime->format('Y-m-d\TH:i:s.v\Z'),
      'operation' => $this->operation,
      'message' => $this->message,
      'target' => $this->target,
    ];
    if ($this->actor) {
      $data['actor'] = $this->actor;
    }
    if (!empty($this->extra)) {
      $data['extra'] = $this->extra;
    }
    return $data;
  }

}

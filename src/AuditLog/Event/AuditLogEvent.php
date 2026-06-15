<?php

declare (strict_types=1);

namespace Drupal\helfi_api_base\AuditLog\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event class for audit log use.
 *
 * Event subscribers may enrich the event before
 * it is written to the audit log.
 *
 * @see https://helsinkisolutionoffice.atlassian.net/wiki/spaces/platta/pages/10189438980/Implementing+audit+logging
 * @see \Drupal\helfi_api_base\AuditLog\EventSubscriber\AuditLogActorSubscriber
 * @see \Drupal\helfi_api_base\AuditLog\AuditLogService::logOperation()
 */
class AuditLogEvent extends Event {

  /**
   * The acting user, injected by event subscribers.
   *
   * @var array<string, mixed>
   */
  protected array $actor = [];

  /**
   * Construct a new event object.
   *
   * @param string $operation
   *   The operation being logged.
   * @param string $status
   *   The status of the operation.
   * @param array<string, mixed> $target
   *   The target of the operation.
   * @param string $origin
   *   String identifying the source for the audit log message.
   */
  public function __construct(
    public readonly string $operation,
    public readonly string $status,
    public readonly array $target,
    protected string $origin = 'DRUPAL',
  ) {
  }

  /**
   * Set the acting user.
   *
   * @param array<string, mixed> $actor
   *   The actor associated with the event.
   */
  public function setActor(array $actor): void {
    $this->actor = $actor;
  }

  /**
   * Get the acting user.
   *
   * @return array<string, mixed>
   *   The actor associated with the event.
   */
  public function getActor(): array {
    return $this->actor;
  }

  /**
   * Get message data.
   *
   * @return array<string, mixed>
   *   Message associated with the event.
   */
  public function getMessage(): array {
    $message = [
      'operation' => $this->operation,
      'status' => $this->status,
      'target' => $this->target,
    ];
    if ($this->actor !== []) {
      $message['actor'] = $this->actor;
    }
    return $message;
  }

  /**
   * Get origin.
   */
  public function getOrigin(): string {
    return $this->origin;
  }

  /**
   * Set origin.
   *
   * @param string $origin
   *   String identifying the source for the audit log message.
   */
  public function setOrigin(string $origin): void {
    $this->origin = $origin;
  }

}

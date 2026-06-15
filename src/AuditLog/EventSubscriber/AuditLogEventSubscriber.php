<?php

declare (strict_types=1);

namespace Drupal\helfi_api_base\AuditLog\EventSubscriber;

use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber for audit log events.
 */
readonly class AuditLogEventSubscriber implements EventSubscriberInterface {

  public function __construct(
    private AuditLogServiceInterface $auditLogService,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[AuditLogEvent::LOG][] = ['writeToDatabase', -100];
    return $events;
  }

  /**
   * Write log message to database.
   *
   * This method is called whenever the AuditEvent::LOG event is
   * dispatched.
   */
  public function writeToDatabase(AuditLogEvent $event): void {
    $this->auditLogService->logOperation($event->getMessage(), $event->getOrigin());
  }

}

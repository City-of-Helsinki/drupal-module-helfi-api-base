<?php

declare (strict_types=1);

namespace Drupal\helfi_api_base\AuditLog\EventSubscriber;

use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use LoggerExtra\LoggerContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds the logger context request id to audit log events.
 *
 * @see \LoggerExtra\RequestIdMiddleware
 */
readonly class AuditLogRequestIdSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      AuditLogEvent::class => ['addRequestId', 10],
    ];
  }

  /**
   * Adds the request id to the event.
   */
  public function addRequestId(AuditLogEvent $event): void {
    if (!isset($event->extra['request_id']) && $requestId = LoggerContext::get()['request_id'] ?? NULL) {
      $event->extra['request_id'] = $requestId;
    }
  }

}

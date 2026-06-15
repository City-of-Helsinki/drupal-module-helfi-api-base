<?php

namespace Drupal\helfi_api_base\AuditLog\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber for audit log events.
 *
 * For valid events this subscriber calls related service which
 * handles database writing. For invalid message Drupal log entry is generated.
 */
class AuditLogEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * AuditLogService.
   *
   * @var Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface
   */
  protected AuditLogServiceInterface $auditLogService;

  /**
   * Logger service.
   *
   * @var Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Construct new AuditLogEventSubscriber.
   *
   * @param \Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface $auditLogService
   *   Service that handles writing to the audit log.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger for logging messages to Drupal logs.
   */
  public function __construct(AuditLogServiceInterface $auditLogService, LoggerInterface $logger) {
    $this->auditLogService = $auditLogService;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AuditLogEvent::LOG][] = ['writeToDatabase', -100];
    return $events;
  }

  /**
   * Write log message to database.
   *
   * This method is called whenever the AuditEvent::LOG event is
   * dispatched.
   *
   * @param \Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent $event
   *   Event to handle.
   */
  public function writeToDatabase(AuditLogEvent $event): void {
    if (!$event->isValid()) {
      $this->logger->error($this->t('Audit log message validation failed.'));
      return;
    }
    $this->auditLogService->logOperation($event->getMessage(), $event->getOrigin());
  }

}

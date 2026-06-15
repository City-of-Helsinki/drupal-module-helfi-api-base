<?php

declare (strict_types=1);

namespace Drupal\helfi_api_base\AuditLog;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Connection;
use Drupal\Core\Utility\Error;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * AuditLog service.
 */
readonly class AuditLogService implements AuditLogServiceInterface {

  /**
   * Constructs a AuditLogService object.
   */
  public function __construct(
    private Connection $connection,
    private TimeInterface $time,
    private EventDispatcherInterface $eventDispatcher,
    #[Autowire(service: 'logger.channel.helfi_api_base')]
    private LoggerInterface $logger,
  ) {
  }

  /**
   * Dispatch AuditLogEvent and write the resulting message to the database.
   *
   * @param \Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent $event
   *   The audit log event to log.
   */
  public function logOperation(AuditLogEvent $event): void {
    $this->eventDispatcher->dispatch($event);

    try {
      $this->connection->insert('helfi_audit_logs')
        ->fields([
          'created_at' => gmdate('Y-m-d H:i:s', $this->time->getRequestTime()),
          'is_sent' => 0,
          'message' => Json::encode(['audit_event' => $event->getData()]),
        ])
        ->execute();
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }
  }

}

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
   * Dispatch AuditLogEvent.
   *
   * @param array<string, mixed> $message
   *   Message associated with the event.
   */
  public function dispatchEvent(array $message): void {
    $event = new AuditLogEvent($message);
    $this->eventDispatcher->dispatch($event, AuditLogEvent::LOG);
  }

  /**
   * Operation that logs the message to database.
   *
   * @param array<string, mixed> $message
   *   Message that is merged with generic data and logged to database.
   * @param string $origin
   *   String identifying the source for the audit log message.
   */
  public function logOperation(array $message, string $origin): void {
    $current_timestamp = $this->time->getCurrentMicroTime();

    $operation_data = [
      "origin" => $origin,
      "source" => "DRUPAL",
      "date_time_epoch" => floor($current_timestamp * 1000),
      // Format should be yyyy-MM-ddThh:mm:ss.SSSZ.
      "date_time" =>
      gmdate("Y-m-d\TH:i:s", (int) floor($current_timestamp)) .
      "." .
      str_pad((string) (int) floor(($current_timestamp - floor($current_timestamp)) * 1000), 3, "0", STR_PAD_LEFT) .
      "Z",
    ];

    // Merge message and generic operation data.
    $operation_data = array_merge($operation_data, $message);

    try {
      $this->connection->insert('helfi_audit_logs')
        ->fields([
          'created_at' => gmdate('Y-m-d H:i:s', $this->time->getRequestTime()),
          'is_sent' => 0,
          'message' => Json::encode(['audit_event' => $operation_data]),
        ])
        ->execute();
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }
  }

}

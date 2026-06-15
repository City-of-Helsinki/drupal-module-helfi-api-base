<?php

namespace Drupal\helfi_api_base\AuditLog;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * AuditLog service.
 */
class AuditLogService implements AuditLogServiceInterface {

  use StringTranslationTrait;
  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected Request $request;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a AuditLogService object.
   */
  public function __construct(
    AccountProxyInterface $accountProxy,
    Connection $connection,
    TimeInterface $time,
    RequestStack $requestStack,
    EventDispatcherInterface $eventDispatcher,
    LoggerInterface $logger,
  ) {
    $this->currentUser = $accountProxy;
    $this->connection = $connection;
    $this->time = $time;
    $this->request = $requestStack->getCurrentRequest();
    $this->eventDispatcher = $eventDispatcher;
    $this->logger = $logger;
  }

  /**
   * Dispatch AuditLogEvent.
   *
   * @param array $message
   *   Message associated with the event.
   */
  public function dispatchEvent(array $message): void {
    $event = new AuditLogEvent($message);
    $this->eventDispatcher->dispatch($event, AuditLogEvent::LOG);
  }

  /**
   * Operation that logs the message to database.
   *
   * @param array $message
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
      gmdate("Y-m-d\TH:i:s", floor($current_timestamp)) .
      "." .
      str_pad(floor(($current_timestamp - floor($current_timestamp)) * 1000), 3, "0", STR_PAD_LEFT) .
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
      $this->logger
        ->error($this->t('Unable to write log message to database.'));
    }
  }

}

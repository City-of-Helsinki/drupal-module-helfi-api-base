<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\AuditLog\Sources;

use Drupal\Core\Database\Database;
use ResilientLogger\Sources\AbstractLogSourceEntry;
use ResilientLogger\Utils\Helpers;

/**
 * Implements audit logging source entry.
 *
 * @see \ResilientLogger\Sources\AbstractLogSourceEntry
 *
 * @phpstan-import-type LogSourceConfig from \ResilientLogger\Sources\Types
 * @phpstan-import-type AuditLogDocument from \ResilientLogger\Sources\Types
 */
class HelfiAuditLogSourceEntry implements AbstractLogSourceEntry {
  private const string TABLE_NAME = 'helfi_audit_logs';
  private const array KNOWN_KEYS = [
    'actor',
    'date_time',
    'operation',
    'target',
    'message',
    'extra',
  ];

  /**
   * Constructs new entry instance, called by HelfiAuditLogSource.
   *
   * @param int $id
   *   Entry's database primary key.
   * @param LogSourceConfig $config
   *   Source configuration for environment details.
   */
  public function __construct(
    private readonly int $id,
    private readonly array $config,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * Returns the document in question mapped in standard format.
   *
   * @return AuditLogDocument
   *   Entry data in standard mapped format.
   */
  public function getDocument(): array {
    /** @var array<string, mixed>|false $result */
    $result = Database::getConnection()
      ->select(self::TABLE_NAME, 'h')
      ->fields('h')
      ->condition('id', $this->id)
      ->execute()
      ->fetch(\PDO::FETCH_ASSOC);

    if (!$result) {
      throw new \RuntimeException("Document not found");
    }

    $timestamp = strtotime($result['created_at']);
    $createdAt = (new \DateTimeImmutable('@' . $timestamp))
      ->setTimezone(new \DateTimeZone('UTC'));

    $message = json_decode($result['message'], TRUE);
    $data = array_intersect_key(
      $message["audit_event"],
      array_flip(self::KNOWN_KEYS)
    );

    return [
      "@timestamp" => $createdAt,
      "audit_event" => [
        "actor" => Helpers::valueAsArray($data["actor"]),
        "date_time" => $data["date_time"],
        "operation" => $data["operation"],
        "origin" => $this->config["origin"],
        "target" => Helpers::valueAsArray($data["target"]),
        "environment" => $this->config["environment"],
        "message" => $data["message"],
        "level" => 0,
        "extra" => array_merge(
          // Unknown keys gets added to extra field.
          array_diff_key($message["audit_event"], array_flip(self::KNOWN_KEYS)),
          // Extra fields specified in the message.
          $data['extra'] ?? [],
          // Database primary key.
          [
            "source_pk" => $this->id,
          ],
        ),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isSent(): bool {
    /** @var array<string, mixed>|false $result */
    $result = Database::getConnection()
      ->select(self::TABLE_NAME, 'h')
      ->fields('h', ['is_sent'])
      ->condition('id', $this->id)
      ->execute()
      ->fetch(\PDO::FETCH_ASSOC);

    return (bool) ($result['is_sent'] ?? FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function markSent(): void {
    Database::getConnection()
      ->update(self::TABLE_NAME)
      ->fields(['is_sent' => 1])
      ->condition('id', $this->id)
      ->execute();
  }

}

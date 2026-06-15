<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\AuditLog\Sources;

use Drupal\Core\Database\Database;
use ResilientLogger\Sources\AbstractLogSource;
use ResilientLogger\Sources\AbstractLogSourceEntry;

/**
 * Implements audit logging source.
 *
 * @see \ResilientLogger\Sources\AbstractLogSource
 *
 * @phpstan-import-type LogSourceConfig from \ResilientLogger\Sources\Types
 * @phpstan-import-type AuditLogDocument from \ResilientLogger\Sources\Types
 */
class HelfiAuditLogSource implements AbstractLogSource {
  private const TABLE_NAME = 'helfi_audit_logs';

  /**
   * Constructs new HelfiAuditLogSource.
   *
   * @param LogSourceConfig $config
   *   Configuration and environment information passed during initialization.
   */
  public function __construct(private array $config) {}

  /**
   * Creates new entry for this given source.
   *
   * @param int $level
   *   Log level.
   * @param mixed $message
   *   Message.
   * @param array<string, mixed> $context
   *   Extra context.
   *
   * @return never
   *   Unsupported operation.
   *
   * @throws \LogicException
   *   Thrown logic exception, operation is not supported.
   */
  public function create(int $level, mixed $message, array $context = []): AbstractLogSourceEntry {
    throw new \LogicException(sprintf('%s does not support create().', static::class));
  }

  /**
   * {@inheritdoc}
   *
   * @return \Generator<AbstractLogSourceEntry>
   *   Generator of unsent entries.
   */
  public function getUnsentEntries(int $chunkSize): \Generator {
    $results = Database::getConnection()
      ->select(self::TABLE_NAME, 'h')
      ->fields('h', ['id'])
      ->condition('is_sent', 0)
      ->range(0, $chunkSize)
      ->orderBy('id', 'ASC')
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($results as $result) {
      yield new HelfiAuditLogSourceEntry(intval($result['id']), $this->config);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearSentEntries(int $daysToKeep): void {
    $olderThan = gmdate('Y-m-d H:i:s', time() - ($daysToKeep * 86400));
    Database::getConnection()
      ->delete(self::TABLE_NAME)
      ->condition('is_sent', 1)
      ->condition('created_at', $olderThan, '<=')
      ->execute();
  }

}

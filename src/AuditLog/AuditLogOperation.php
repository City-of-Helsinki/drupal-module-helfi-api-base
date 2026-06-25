<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\AuditLog;

/**
 * Operations that can be written to the audit log.
 */
enum AuditLogOperation: string {
  case Read = 'READ';
  case Create = 'CREATE';
  case Update = 'UPDATE';
  case Delete = 'DELETE';

  /**
   * Operations logged by default when an entity type lists no explicit ones.
   *
   * READ is intentionally excluded: reads are noisy and must be opted into
   * per entity type via the 'operations' key.
   *
   * @return array<self>
   *   The default entity operations.
   */
  public static function defaultEntityOperations(): array {
    return [self::Create, self::Update, self::Delete];
  }

}

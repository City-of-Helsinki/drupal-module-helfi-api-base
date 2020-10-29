<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base;

/**
 * Helper trait to deal with migrations.
 */
trait MigrateTrait {

  /**
   * Checks if we're doing partial migrate.
   *
   * @return bool
   *   TRUE if partial migrate.
   */
  public function isPartialMigrate() : bool {
    $is_partial = getenv('PARTIAL_MIGRATE');

    return in_array($is_partial, ['true', '1']);
  }

}

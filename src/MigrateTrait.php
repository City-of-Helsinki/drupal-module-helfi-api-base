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

  /**
   * Marks the migrate as partial or not.
   *
   * @param bool $status
   *   TRUE if partial migrate, FALSE if not.
   */
  public function setIsPartialMigrate(bool $status = TRUE) : void {
    putenv('PARTIAL_MIGRATE=' . ($status ? '1' : '0'));
  }

}

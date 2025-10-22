<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Debug;

/**
 * An interface to indicate this debug plugin has a validity check.
 */
interface SupportsValidityChecksInterface {

  /**
   * Converts debug item into boolean value.
   *
   * @return bool
   *   True if the debug test should pass.
   */
  public function check() : bool;

}

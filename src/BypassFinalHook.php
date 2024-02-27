<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

/**
 * Allow final classes to be mocked.
 */
final class BypassFinalHook implements BeforeTestHook {

  /**
   * {@inheritdoc}
   */
  public function executeBeforeTest(string $test): void {
    BypassFinals::enable();
  }

}

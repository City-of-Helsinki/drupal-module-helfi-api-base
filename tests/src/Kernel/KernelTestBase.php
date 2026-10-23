<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\KernelTestBase as CoreKernelTestBase;

/**
 * Base class for helfi_api_base kernel tests.
 */
abstract class KernelTestBase extends CoreKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'diff',
    'helfi_api_base',
  ];

}

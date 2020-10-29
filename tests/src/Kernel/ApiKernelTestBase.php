<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Tests\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * API test base.
 */
abstract class ApiKernelTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'api_tools',
  ];

}

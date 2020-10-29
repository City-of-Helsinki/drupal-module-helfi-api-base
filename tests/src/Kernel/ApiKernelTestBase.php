<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

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
    'helfi_api_base',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['helfi_api_base']);
    $this->installSchema('system', ['sequences']);
  }

}

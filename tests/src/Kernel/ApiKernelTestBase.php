<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * API test base.
 */
abstract class ApiKernelTestBase extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'api_tools',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['helfi_api_base']);
  }

}

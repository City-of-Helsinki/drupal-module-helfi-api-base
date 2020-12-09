<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;

/**
 * API test base.
 */
abstract class ApiKernelTestBase extends EntityKernelTestBase {

  use ApiTestTrait;

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
  protected function setUp() : void {
    parent::setUp();

    $this->installConfig(['helfi_api_base']);
  }

}

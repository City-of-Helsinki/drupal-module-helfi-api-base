<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Tests\helfi_api_base\Traits\DefaultConfigurationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Existing site test base.
 */
abstract class ExistingSiteTestBase extends ExistingSiteBase {

  use DefaultConfigurationTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->setupDefaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() : void {
    parent::tearDown();
    $this->tearDownDefaultConfiguration();
  }

}

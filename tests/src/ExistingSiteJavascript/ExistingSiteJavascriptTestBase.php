<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\ExistingSiteJavascript;

use Drupal\Tests\helfi_api_base\Traits\DefaultConfigurationTrait;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Existing site test base.
 */
abstract class ExistingSiteJavascriptTestBase extends ExistingSiteWebDriverTestBase {

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

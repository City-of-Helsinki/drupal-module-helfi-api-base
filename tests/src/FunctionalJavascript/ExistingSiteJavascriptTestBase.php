<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\FunctionalJavascript;

use Drupal\Tests\helfi_api_base\Traits\DefaultConfigurationTrait;
use weitzman\DrupalTestTraits\ExistingSiteSelenium2DriverTestBase;

/**
 * Existing site test base.
 */
abstract class ExistingSiteJavascriptTestBase extends ExistingSiteSelenium2DriverTestBase {

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

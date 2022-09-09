<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\LanguageManagerTrait;

/**
 * Tests custom language negotiator functionality.
 */
class LanguageNegotiatorTest extends KernelTestBase {

  use LanguageManagerTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'language',
    'config_translation',
    'content_translation',
    'helfi_language_negotiator_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->setupLanguages();
  }

  /**
   * Make sure language negotiator can override the currently active language.
   */
  public function testLanguageOverride() : void {
    $this->assertEquals('en', $this->languageManager()->getCurrentLanguage()->getId());

    foreach (['fi', 'sv'] as $language) {
      $this->setOverrideLanguageCode($language);
      $this->assertEquals($language, $this->languageManager()->getCurrentLanguage()->getId());
    }
  }

}

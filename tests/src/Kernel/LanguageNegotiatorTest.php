<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\Language\LanguageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\LanguageManagerTrait;

/**
 * Tests custom language negotiator functionality.
 *
 * @group helfi_api_base
 */
class LanguageNegotiatorTest extends KernelTestBase {

  use LanguageManagerTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'helfi_language_negotiator_test',
    'language',
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
    $this->assertEquals('en', $this->languageManager()->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)->getId());

    foreach (['fi', 'sv'] as $language) {
      $this->setOverrideLanguageCode($language);
      $this->assertEquals($language, $this->container->get('language_manager')->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)->getId());
    }
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\LanguageManagerTrait;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests language resolver functionality.
 *
 * @group helfi_api_base
 */
class DefaultLanguagesTest extends KernelTestBase {

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
   * Make sure default language parameters are loaded properly.
   */
  public function testDefaultLanguageResolver() : void {
    /** @var \Drupal\helfi_api_base\Language\DefaultLanguageResolver $language_resolver */
    $language_resolver = $this->container->get('helfi_api_base.default_language_resolver');

    // Check that default parameters are correct.
    $this->assertEquals('en', $language_resolver->getFallbackLanguage());
    $this->assertEquals(['en', 'fi', 'sv'], $language_resolver->getDefaultLanguages());
    $this->assertFalse($language_resolver->isAltLanguage());

    // Create new language and check that it is considered non-standard.
    ConfigurableLanguage::createFromLangcode('xx')->save();
    $this->setOverrideLanguageCode('xx');
    $this->assertTrue($language_resolver->isAltLanguage());
  }

}

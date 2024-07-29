<?php

namespace Drupal\Tests\helfi_api_base\Unit\Language;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\helfi_api_base\Language\DefaultLanguageResolver;
use Drupal\helfi_api_base\Language\DefaultLanguageResolverInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests helfi_api_base_template_preprocess_default_variables_alter().
 *
 * @group helfi_api_base
 */
class DefaultLanguageVariablesTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * The language manager mock.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $languageManager;

  /**
   * The default language resolver mock.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $defaultLanguageResolver;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a container.
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);

    // Mock the language manager.
    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $container->set('language_manager', $this->languageManager->reveal());

    // Mock the default language resolver.
    $this->defaultLanguageResolver = $this->prophesize(DefaultLanguageResolverInterface::class);
    $container->set('helfi_api_base.default_language_resolver', $this->defaultLanguageResolver->reveal());

    // Include the helfi_api_base module file where the
    // helfi_api_base_template_preprocess_default_variables_alter()
    // function is defined.
    require_once __DIR__ . '/../../../../helfi_api_base.module';
  }

  /**
   * Tests preprocess default variables alter with a default language.
   */
  public function testPreprocessDefaultVariablesAlterForDefaultLanguage() {
    $variables = [];

    // Set up the language mock.
    $language = new Language([
      'id' => 'fi',
      'name' => 'Finnish',
      'direction' => LanguageInterface::DIRECTION_LTR,
    ]);
    $this->setupLanguageMocks($language);

    // Include the module file where the function is defined.
    helfi_api_base_template_preprocess_default_variables_alter($variables);

    // Assert the variables are set correctly.
    $this->assertEquals($language->getId(), $variables['language']->getId());
    $this->assertFalse($variables['alternative_language']);
  }

  /**
   * Tests preprocess default variables alter with an alternative language.
   */
  public function testPreprocessDefaultVariablesAlterForAlternativeLanguage() {
    $variables = [];

    // Set up the language mock.
    $language = new Language([
      'id' => 'fa',
      'name' => 'Farsi',
      'direction' => LanguageInterface::DIRECTION_RTL,
    ]);
    $this->setupLanguageMocks($language);

    // Include the module file where the function is defined.
    helfi_api_base_template_preprocess_default_variables_alter($variables);

    // Assert the variables are set correctly.
    $this->assertEquals($language->getId(), $variables['language']->getId());
    $this->assertTrue($variables['alternative_language']);

    // Prophesize the default language for the DefaultLanguageResolver.
    $languageManager = $this->prophesize(LanguageManagerInterface::class);
    $languageManager->getCurrentLanguage()->willReturn($language);
    $languageManager->getLanguage('en')->willReturn(new Language([
      'id' => 'en',
      'name' => 'English',
      'direction' => LanguageInterface::DIRECTION_LTR,
    ]));
    $sut = new DefaultLanguageResolver(['en', 'sv', 'fi'], 'en', $languageManager->reveal());
    $attributes = $sut->getFallbackLangAttributes();

    // Assert the fallback language attributes are set correctly.
    $this->assertEquals($attributes['lang'], $variables['lang_attributes']['fallback_lang']);
    $this->assertEquals($attributes['dir'], $variables['lang_attributes']['fallback_dir']);
  }

  /**
   * Sets up language mocks.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language mock.
   */
  protected function setupLanguageMocks(LanguageInterface $language): void {
    // Set up the current language mock.
    $this->languageManager
      ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
      ->willReturn($language);

    // Set up the mock for the default language resolver.
    $this->defaultLanguageResolver
      ->isAltLanguage($language->getId())
      ->willReturn($language->getId() !== 'fi');
    $this->defaultLanguageResolver
      ->getFallbackLangAttributes()
      ->willReturn([
        'lang' => 'en',
        'dir' => 'ltr',
      ]);
  }

}

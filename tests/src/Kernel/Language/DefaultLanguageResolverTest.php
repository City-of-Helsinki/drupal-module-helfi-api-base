<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Language;

use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Language\DefaultLanguageResolver
 * @group helfi_api_base
 */
class DefaultLanguageResolverTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * @covers ::__construct
   * @covers ::getDefaultLanguages
   * @covers ::getFallbackLanguage
   */
  public function testContainerParameters() : void {
    $defaultLanguages = $this->container->getParameter('helfi_api_base.default_languages');
    $fallbackLanguage = $this->container->getParameter('helfi_api_base.fallback_language');
    /** @var \Drupal\helfi_api_base\Language\DefaultLanguageResolver $sut */
    $sut = $this->container->get('helfi_api_base.default_language_resolver');

    $this->assertEquals($defaultLanguages, $sut->getDefaultLanguages());
    $this->assertEquals($fallbackLanguage, $sut->getFallbackLanguage());
  }

}

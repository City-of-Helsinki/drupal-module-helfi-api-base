<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\helfi_api_base\Language\DefaultLanguageResolver;
use Drupal\Tests\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests Default language resolver.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Language\DefaultLanguageResolver
 * @group helfi_api_base
 */
class DefaultLanguageResolverTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * @covers ::__construct
   * @covers ::getDefaultLanguages
   * @covers ::getFallbackLanguage
   *
   * @dataProvider getDefaultLanguagesData
   */
  public function testGetDefaultLanguages(
    array $expectedLanguages,
    string $expectedLanguage,
    array $defaultLanguages,
    string $fallbackLanguage,
  ) : void {
    $sut = new DefaultLanguageResolver(
      $defaultLanguages,
      $fallbackLanguage,
      $this->prophesize(LanguageManagerInterface::class)->reveal(),
    );
    $this->assertEquals($expectedLanguages, $sut->getDefaultLanguages());
    $this->assertEquals($expectedLanguage, $sut->getFallbackLanguage());
  }

  /**
   * Data provider for default language data.
   *
   * @return array
   *   The data.
   */
  public function getDefaultLanguagesData() : array {
    return [
      // Test empty.
      [
        [],
        '',
        [],
        '',
      ],
      [
        ['en', 'sv', 'fi'],
        'en',
        ['en', 'sv', 'fi'],
        'en',
      ],
    ];
  }

  /**
   * @covers ::isAltLanguage
   * @covers ::__construct
   */
  public function testIsAltLanguage() : void {
    $languageManager = $this->prophesize(LanguageManagerInterface::class);
    $languageManager->getCurrentLanguage()
      ->willReturn(new Language(Language::$defaultValues))
      ->shouldBeCalledTimes(2);

    $sut = new DefaultLanguageResolver(['en', 'sv', 'fi'], 'en', $languageManager->reveal());

    $this->assertTrue($sut->isAltLanguage('kr'));
    $this->assertFalse($sut->isAltLanguage());
    $this->assertFalse($sut->isAltLanguage(''));
    $this->assertFalse($sut->isAltLanguage('fi'));
  }

  /**
   * @covers ::__construct
   * @covers ::getFallbackLangAttributes
   */
  public function testGetFallBackLangAttributes() : void {
    $languageManager = $this->prophesize(LanguageManagerInterface::class);
    $languageManager->getLanguage('en')
      ->willReturn(new Language(Language::$defaultValues))
      ->shouldBeCalled();
    $sut = new DefaultLanguageResolver(['en', 'sv', 'fi'], 'en', $languageManager->reveal());
    $this->assertEquals([
      'lang' => Language::$defaultValues['id'],
      'dir' => Language::$defaultValues['direction'],
    ], $sut->getFallbackLangAttributes());
  }

  /**
   * @covers ::__construct
   * @covers ::getCurrentLangAttributes
   */
  public function testGetCurrentLangAttributes() : void {
    $languageManager = $this->prophesize(LanguageManagerInterface::class);
    $languageManager->getCurrentLanguage()
      ->willReturn(new Language(Language::$defaultValues))
      ->shouldBeCalled();

    $sut = new DefaultLanguageResolver(['en', 'sv', 'fi'], 'en', $languageManager->reveal());
    $this->assertEquals([
      'lang' => Language::$defaultValues['id'],
      'dir' => Language::$defaultValues['direction'],
    ], $sut->getCurrentLangAttributes());
  }

}

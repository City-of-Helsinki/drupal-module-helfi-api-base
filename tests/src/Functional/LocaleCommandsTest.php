<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Tests locale commands.
 *
 * @group helfi_api_base
 */
class LocaleCommandsTest extends BrowserTestBase {

  use DrushTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'locale',
    'helfi_api_base',
    'helfi_locale_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    foreach (['fi', 'sv'] as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
  }

  /**
   * Tests helfi:locale-import command.
   */
  public function testImport() {
    $this->drush('helfi:locale-import', ['helfi_locale_test']);
    // Invalidate translation cache.
    Cache::invalidateTags(['locale']);

    $strings = [
      'fi' => [
        'Test string' => 'Test string fi',
        'Test string 2' => 'Test string 2 fi',
      ],
      'sv' => [
        'Test string' => 'Test string sv',
      ],
    ];

    foreach ($strings as $langcode => $group) {
      foreach ($group as $source => $expected) {
        // @codingStandardsIgnoreLine
        $string = new TranslatableMarkup($source, [], ['langcode' => $langcode]);
        $this->assertEquals($expected, (string) $string);
      }
    }

    $singular = new PluralTranslatableMarkup(1, 'One test item', '@count test items', [], ['langcode' => 'fi']);
    $plural = new PluralTranslatableMarkup(5, 'One test item', '@count test items', [], ['langcode' => 'fi']);

    $this->assertEquals('Yksi testi itemi', (string) $singular);
    $this->assertEquals('5 testi itemiä', (string) $plural);

    // String 'Test' should have finnish translation in Helfi context.
    $context_fi = new TranslatableMarkup('Test', [], [
      'langcode' => 'fi',
      'context' => 'Helfi',
    ]);
    $this->assertEquals('Testihelfi', (string) $context_fi);

    // String 'Test' shouldn't have swedish translation in Helfi context.
    $context_sv = new TranslatableMarkup('Test', [], [
      'langcode' => 'sv',
      'context' => 'Helfi',
    ]);
    $this->assertEquals('Test', (string) $context_sv);
  }

}

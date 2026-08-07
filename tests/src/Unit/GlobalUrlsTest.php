<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\GlobalUrls;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests GlobalUrls.
 *
 * @group helfi_api_base
 * @coversDefaultClass \Drupal\helfi_api_base\GlobalUrls
 */
class GlobalUrlsTest extends UnitTestCase {

  /**
   * Expected keys present in every language's URL array.
   */
  private const EXPECTED_KEYS = [
    'events_link_url',
    'decisions_link_url',
    'jobs_link_url',
    'contact_link_url',
    'helsinki_near_you_link_url',
    'ai_register_url',
    'helfi_search_form_url',
    'helfi_ai_search_form_url',
    'error_page_home_link',
    'error_page_feedback_link',
  ];

  /**
   * Tests that all expected keys are present for each supported language.
   *
   * @covers ::get
   */
  #[DataProvider('langcodeProvider')]
  public function testGetReturnsAllKeys(string $langcode): void {
    $urls = GlobalUrls::get($langcode);
    foreach (self::EXPECTED_KEYS as $key) {
      $this->assertArrayHasKey($key, $urls, "Missing key '$key' for langcode '$langcode'.");
      $this->assertNotEmpty($urls[$key], "Empty value for key '$key' in langcode '$langcode'.");
    }
  }

  /**
   * Tests that each language returns distinct URLs where expected.
   *
   * @covers ::get
   */
  public function testLanguagesReturnDistinctUrls(): void {
    $fi = GlobalUrls::get('fi');
    $sv = GlobalUrls::get('sv');
    $en = GlobalUrls::get('en');

    $this->assertNotEquals($fi['helfi_search_form_url'], $en['helfi_search_form_url']);
    $this->assertNotEquals($sv['helfi_search_form_url'], $en['helfi_search_form_url']);
    $this->assertNotEquals($fi['ai_register_url'], $sv['ai_register_url']);
  }

  /**
   * Tests that an unknown langcode falls back to English.
   *
   * @covers ::get
   */
  public function testUnknownLangcodeFallsBackToEnglish(): void {
    $this->assertEquals(GlobalUrls::get('en'), GlobalUrls::get('xx'));
  }

  /**
   * Data provider for supported language codes.
   *
   * @return array<string, array<string>>
   *   Language codes to test.
   */
  public static function langcodeProvider(): array {
    return [
      'finnish' => ['fi'],
      'swedish' => ['sv'],
      'english' => ['en'],
    ];
  }

}

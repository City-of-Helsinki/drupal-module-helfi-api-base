<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\GlobalUrl;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests GlobalUrl.
 *
 * @group helfi_api_base
 * @coversDefaultClass \Drupal\helfi_api_base\GlobalUrl
 */
class GlobalUrlTest extends UnitTestCase {

  /**
   * Tests that every case resolves to a URL for each supported language.
   *
   * @covers ::url
   */
  #[DataProvider('langcodeProvider')]
  public function testUrlReturnsStringForEveryCase(string $langcode): void {
    foreach (GlobalUrl::cases() as $case) {
      $url = $case->url($langcode);
      $this->assertIsString($url, "Non-string value for case '{$case->name}' in langcode '$langcode'.");
    }
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

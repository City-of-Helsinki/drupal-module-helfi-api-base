<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\ServiceMap;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\helfi_api_base\ServiceMap\ServiceMap;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;

/**
 * Tests Address sanitization regex.
 */
#[Group('helfi_api_base')]
class AddressSanitizeTest extends UnitTestCase {

  /**
   * Sanitized the given string.
   *
   * @param string $input
   *   The input.
   *
   * @return string
   *   The sanitized string.
   */
  private function sanitize(string $input): string {
    $serviceMap = new ServiceMap(
      $this->prophesize(ClientInterface::class)->reveal(),
      $this->prophesize(LanguageManagerInterface::class)->reveal(),
      $this->prophesize(LoggerInterface::class)->reveal(),
    );
    return $serviceMap->sanitizeAddress($input);
  }

  /**
   * Tests that allowed characters are preserved.
   */
  #[DataProvider('allowedCharactersProvider')]
  public function testAllowedCharactersArePreserved(string $input): void {
    $this->assertSame($input, $this->sanitize($input));
  }

  /**
   * Data provider for allowed characters.
   *
   * @return array<mixed>
   *   The test data.
   */
  public static function allowedCharactersProvider(): array {
    return [
      'ascii letters' => ['Hello World'],
      'numbers' => ['abc 123'],
      'dot' => ['e.g. something'],
      'comma' => ['one, two, three'],
      'single quote' => ["it's fine"],
      'plus' => ['a+b'],
      'minus' => ['a-b'],
      'ampersand' => ['cats & dogs'],
      'pipe' => ['a|b'],
      'unicode letters fi' => ['ääkköset öljy'],
      'unicode letters sv' => ['åland'],
      'cyrillic letters' => ['Привет мир'],
      'mixed allowed' => ["Hello, it's a test +1 & more | less-done."],
    ];
  }

  /**
   * Tests that disallowed characters are removed.
   */
  #[DataProvider('disallowedCharactersProvider')]
  public function testDisallowedCharactersAreRemoved(string $input, string $expected): void {
    $this->assertSame($expected, $this->sanitize($input));
  }

  /**
   * Data provider for disallowed characters.
   *
   * @return array<mixed>
   *   The data.
   */
  public static function disallowedCharactersProvider(): array {
    return [
      'exclamation mark' => ['Hello!', 'Hello'],
      'at sign' => ['user@example.com', 'userexample.com'],
      'hash' => ['#tag', 'tag'],
      'dollar' => ['$100', '100'],
      'percent' => ['50%', '50'],
      'caret' => ['a^b', 'ab'],
      'asterisk' => ['a*b', 'ab'],
      'parentheses' => ['(test)', 'test'],
      'brackets' => ['[test]', 'test'],
      'curly braces' => ['{test}', 'test'],
      'backslash' => ['a\\b', 'ab'],
      'forward slash' => ['a/b', 'ab'],
      'question mark' => ['why?', 'why'],
      'colon' => ['a:b', 'ab'],
      'semicolon' => ['a;b', 'ab'],
      'less than' => ['a<b', 'ab'],
      'greater than' => ['a>b', 'ab'],
      'tilde' => ['a~b', 'ab'],
      'backtick' => ['a`b', 'ab'],
      'tab' => ["a\tb", 'ab'],
      'newline' => ["a\nb", 'ab'],
      'mixed disallowed' => ['Hello! @world#', 'Hello world'],
    ];
  }

  /**
   * Tests empty string.
   */
  public function testEmptyStringReturnsEmpty(): void {
    $this->assertSame('', $this->sanitize(''));
  }

  /**
   * Tests string with only disallowed characters.
   */
  public function testAllDisallowedReturnsEmpty(): void {
    $this->assertSame('', $this->sanitize('!@#$%^*()'));
  }

}

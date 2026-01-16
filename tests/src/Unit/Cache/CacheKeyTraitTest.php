<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Cache\CacheKeyTrait;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests cache key trait.
 *
 * @group helfi_api_base
 */
class CacheKeyTraitTest extends UnitTestCase {

  use CacheKeyTrait;

  /**
   * @covers \Drupal\helfi_api_base\Cache\CacheKeyTrait::getCacheKey
   * @covers \Drupal\helfi_api_base\Cache\CacheKeyTrait::requestOptionsToCacheKey
   */
  #[DataProvider(methodName: 'cacheKeyData')]
  public function testGetCacheKey(string $expected, string $baseKey, array $options) : void {
    $actual = $this->getCacheKey($baseKey, $options);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testRequestOptionsToCacheKey().
   *
   * @return array[]
   *   The data.
   */
  public static function cacheKeyData() : array {
    return [
      [
        'test:1',
        'test:1',
        [],
      ],
      [
        'test:2:key=value',
        'test:2',
        [
          'key' => 'value',
        ],
      ],
      [
        'test:3:value;key=value',
        'test:3',
        [
          'value',
          [
            'key' => 'value',
          ],
        ],
      ],
      [
        ':key1=value1;key2=value2;key3=value3',
        '',
        [
          'key1' => 'value1',
          'key2' => ['value2', 'key3' => 'value3'],
        ],
      ],
      // Make sure non-scalar values are ignored.
      [
        'test:4:value',
        'test:4',
        [
          'value',
          [
            (object) ['key' => 'value'],
          ],
        ],
      ],
    ];
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\helfi_api_base\Cache\CacheKeyTrait;
use Drupal\Tests\UnitTestCase;

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
   * @dataProvider cacheKeyData
   */
  public function testGetCacheKey(string $expected, string $baseKey, array $options) : void {
    $actual = $this->getCacheKey($baseKey, $options);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testGetCacheKey().
   *
   * @return array[]
   *   The data.
   */
  public function cacheKeyData() : array {
    return [
      [
        'test:1',
        'test:1',
        [],
      ],
      [
        'test:2:ef176a6c424f954fa42d4cde03949897',
        'test:2',
        ['key' => 'value'],
      ],
    ];
  }

  /**
   * @covers \Drupal\helfi_api_base\Cache\CacheKeyTrait::getCacheKey
   * @covers \Drupal\helfi_api_base\Cache\CacheKeyTrait::requestOptionsToCacheKey
   * @dataProvider requestOptionsData
   */
  public function testRequestOptionsToCacheKey(string $expected, $baseKey, array $options) : void {
    $key = $this->requestOptionsToCacheKey($baseKey, $options);
    $this->assertEquals($expected, $key);
  }

  /**
   * Data provider for testRequestOptionsToCacheKey().
   *
   * @return array[]
   *   The data.
   */
  public function requestOptionsData() : array {
    return [
      [
        'test:1',
        'test:1',
        [],
      ],
      [
        'test:2:key=value',
        'test:2:',
        [
          'key' => 'value',
        ],
      ],
      [
        'test:3:value;key=value',
        'test:3:',
        [
          'value',
          [
            'key' => 'value',
          ],
        ],
      ],
      [
        'key1=value1;key2=value2;key3=value3',
        '',
        [
          'key1' => 'value1',
          'key2' => ['value2', 'key3' => 'value3'],
        ],
      ],
    ];
  }

}

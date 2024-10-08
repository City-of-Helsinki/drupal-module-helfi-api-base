<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\helfi_api_base\Link\UrlHelper;

/**
 * Tests Url helper.
 *
 * @group helfi_api_base
 * @coversDefaultClass \Drupal\helfi_api_base\Link\UrlHelper
 */
class UrlHelperTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'system',
    'remote_entity_test',
  ];

  /**
   * Tests URL parsing.
   *
   * @covers ::parse
   * @dataProvider getUrlMap
   */
  public function testParse(string $url, string $expected) : void {
    $url = UrlHelper::parse($url);
    $this->assertEquals($expected, $url->toString(TRUE)->getGeneratedUrl());
  }

  /**
   * @covers ::parse
   */
  public function testInvalidLink() : void {
    $caught = FALSE;
    try {
      UrlHelper::parse('@');
    }
    catch (\InvalidArgumentException $e) {
      $caught = TRUE;
      $this->assertEquals("The URI 'https://@' is malformed.", $e->getMessage());
    }
    $this->assertTrue($caught);
  }

  /**
   * Data provider for ::testIsExternal().
   *
   * @return array[]
   *   The data.
   */
  public function getUrlMap() : array {
    return [
      ['entity:remote_entity_test/1', '/rmt/1'],
      ['internal:/test', '/test'],
      ['https://www.hel.fi', 'https://www.hel.fi'],
      ['/test', '/test'],
      // Make sure scheme defaults to https.
      ['www.hel.fi', 'https://www.hel.fi'],
      ['#123', '#123'],
    ];
  }

}

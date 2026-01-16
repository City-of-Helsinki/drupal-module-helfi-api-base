<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Environment\Address;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests Project value object.
 *
 * @group helfi_api_base
 */
class AddressTest extends UnitTestCase {

  /**
   * Tests getAddress() method.
   */
  #[DataProvider(methodName: 'addressData')]
  public function testGetAddress(string $expected, array $values) : void {
    $this->assertEquals($expected, (new Address(...$values))->getAddress());
  }

  /**
   * A data provider.
   *
   * @return array[]
   *   The data.
   */
  public static function addressData() : array {
    return [
      [
        'https://www.hel.fi',
        [
          'domain' => 'www.hel.fi',
          'protocol' => 'https',
          'port' => 443,
        ],
      ],
      [
        'https://www.hel.fi',
        [
          'domain' => 'www.hel.fi',
        ],
      ],
      [
        'http://www.hel.fi',
        [
          'domain' => 'www.hel.fi',
          'protocol' => 'http',
          'port' => 80,
        ],
      ],
      [
        'http://www.hel.fi:8080',
        [
          'domain' => 'www.hel.fi',
          'protocol' => 'http',
          'port' => 8080,
        ],
      ],
    ];
  }

}

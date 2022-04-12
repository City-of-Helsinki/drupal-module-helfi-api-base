<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\Package;

use Drupal\helfi_api_base\Exception\InvalidPackageException;
use Drupal\helfi_api_base\Package\HelfiPackage;
use Drupal\helfi_api_base\Package\Version;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Tests Helfi package collector.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Package\HelfiPackage
 * @group helfi_api_base
 */
class HelfiPackageTest extends UnitTestCase {

  use ApiTestTrait;

  /**
   * Tests applies().
   *
   * @covers ::applies
   * @covers ::__construct
   * @dataProvider appliesData
   */
  public function testApplies(string $package, bool $expected) : void {
    $sut = new HelfiPackage($this->prophesize(ClientInterface::class)->reveal());
    $this->assertEquals($expected, $sut->applies($package));
  }

  /**
   * Data provider for applies() test.
   *
   * @return array[]
   *   The data.
   */
  public function appliesData() : array {
    return [
      ['drupal/helfi_api_base', TRUE],
      ['drupal/hdbt', TRUE],
      ['drupal/hdbt_admin', TRUE],
      ['city-of-helsinki/hauki', FALSE],
    ];
  }

  /**
   * Tests empty version.
   *
   * @covers ::get
   * @covers ::__construct
   * @covers ::getPackageData
   * @dataProvider emptyVersionData
   */
  public function testEmptyVersion($version) : void {
    $client = $this->createMockHttpClient([
      new Response(body: json_encode([
        'packages' => [
          'drupal/helfi_api_base' => [
            [
              'version' => $version,
            ],
          ],
        ],
      ])),
    ]);
    $sut = new HelfiPackage($client);
    $this->expectException(InvalidPackageException::class);
    $this->expectExceptionMessage('No version data found.');
    $sut->get('drupal/helfi_api_base', '1.2.0');
  }

  /**
   * Data provider for empty version check.
   *
   * @return array
   *   The data.
   */
  public function emptyVersionData() : array {
    return [
      [NULL],
      [1],
      [''],
    ];
  }

  /**
   * Tests that client error returns null.
   *
   * @covers ::get
   * @covers ::__construct
   * @covers ::getPackageData
   */
  public function testException() : void {
    // First we try to fetch stable version, then fallback to dev.
    $client = $this->createMockHttpClient([
      new RequestException('Stable package', new Request('GET', 'test')),
      new RequestException('Dev package', new Request('GET', 'test')),
    ]);
    $sut = new HelfiPackage($client);
    $this->expectException(InvalidPackageException::class);
    $this->expectExceptionMessage('No version data found.');
    $sut->get('drupal/helfi_api_base', '1.2.0');
  }

  /**
   * Tests empty packages.
   *
   * @covers ::get
   * @covers ::__construct
   * @covers ::getPackageData
   */
  public function testEmptyPackage() : void {
    $client = $this->createMockHttpClient([
      new Response(body: json_encode([
        'packages' => [],
      ])),
    ]);
    $sut = new HelfiPackage($client);
    $this->expectException(InvalidPackageException::class);
    $this->expectExceptionMessage('Package not found.');
    $sut->get('drupal/helfi_api_base', '1.2.0');
  }

  /**
   * Tests get().
   *
   * @covers ::__construct
   * @covers ::get
   * @covers \Drupal\helfi_api_base\Package\Version
   * @covers ::getPackageData
   *
   * @dataProvider getData
   */
  public function testGet(
    string $packageName,
    array $packageVersions,
    string $packageVersion,
    string $expectedLatestVersion,
    bool $isLatest
  ) : void {
    $client = $this->createMockHttpClient([
      new Response(body: json_encode([
        'packages' => [
          $packageName => $packageVersions,
        ],
      ])),
    ]);
    $sut = new HelfiPackage($client);
    $result = $sut->get($packageName, $packageVersion);
    $this->assertInstanceOf(Version::class, $result);
    $this->assertEquals($packageName, $result->name);
    $this->assertEquals($expectedLatestVersion, $result->latestVersion);
    $this->assertEquals($isLatest, $result->isLatest);
    $this->assertNotEmpty($result->toArray());
  }

  /**
   * Data provider for get().
   *
   * @return array[]
   *   The data.
   */
  public function getData() : array {
    return [
      // Test with same version.
      [
        'drupal/helfi_api_base',
        [
          [
            'version' => '1.2.0',
          ],
          [
            'version' => '1.3.0',
          ],
        ],
        '1.3.0',
        '1.3.0',
        TRUE,
      ],
      // Test with dev-main.
      [
        'drupal/helfi_api_base',
        [
          [
            'version' => '1.2.0',
          ],
          [
            'version' => '1.3.0',
          ],
        ],
        'dev-main',
        '1.3.0',
        FALSE,
      ],
      // Test with future release.
      [
        'drupal/helfi_api_base',
        [
          [
            'version' => '1.2.0',
          ],
          [
            'version' => '1.3.0',
          ],
        ],
        '1.4.0',
        '1.3.0',
        TRUE,
      ],
      // Test older release.
      [
        'drupal/hdbt',
        [
          [
            'version' => '1.2.0',
          ],
          [
            'version' => '1.3.0',
          ],
        ],
        '1.2.0',
        '1.3.0',
        FALSE,
      ],
    ];
  }

}

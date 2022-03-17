<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\Package;

use Drupal\helfi_api_base\Package\HelfiPackage;
use Drupal\helfi_api_base\Package\Version;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Tests Helfi package collector.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Package\HelfiPackage
 * @group helfi_api_base
 */
class HelfiPackageTest extends UnitTestCase {

  /**
   * Creates an HTTP client.
   *
   * @param \GuzzleHttp\Psr7\Response[]|\GuzzleHttp\Exception\RequestException[] $responses
   *   The responses.
   *
   * @return \GuzzleHttp\ClientInterface
   *   The client.
   */
  private function createClient(array $responses) : ClientInterface {
    $mock = new MockHandler($responses);
    $handler = HandlerStack::create($mock);
    return new Client(['handler' => $handler]);
  }

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
   * @dataProvider emptyVersionData
   */
  public function testEmptyVersion($version) : void {
    $client = $this->createClient([
      new Response(body: json_encode([
        'packages' => [
          [
            'drupal/helfi_api_base' => [
              [
                'version' => $version,
              ],
            ],
          ],
        ],
      ])),
    ]);
    $sut = new HelfiPackage($client);
    $this->assertNull($sut->get('drupal/helfi_api_base', '1.2.0'));
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
   */
  public function testException() : void {
    $client = $this->createClient([
      new RequestException('some error', new Request('GET', 'test')),
    ]);
    $sut = new HelfiPackage($client);
    $this->assertNull($sut->get('drupal/helfi_api_base', '1.2.0'));
  }

  /**
   * Tests empty packages.
   *
   * @covers ::get
   * @covers ::__construct
   */
  public function testEmptyPackage() : void {
    $client = $this->createClient([
      new Response(body: json_encode([
        'packages' => [],
      ])),
    ]);
    $sut = new HelfiPackage($client);
    $this->assertNull($sut->get('drupal/helfi_api_base', '1.2.0'));
  }

  /**
   * Tests get().
   *
   * @covers ::__construct
   * @covers ::get
   * @covers \Drupal\helfi_api_base\Package\Version
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
    $client = $this->createClient([
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

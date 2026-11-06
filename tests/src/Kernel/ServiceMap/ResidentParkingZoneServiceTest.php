<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\ServiceMap;

use Drupal\helfi_api_base\ServiceMap\ResidentParkingZoneService;
use Drupal\helfi_api_base\ServiceMap\DTO\Location;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test parking zone service.
 */
#[CoversClass(ResidentParkingZoneService::class)]
#[RunTestsInSeparateProcesses]
#[Group('helfi_api_base')]
class ResidentParkingZoneServiceTest extends KernelTestBase {

  use ApiTestTrait;

  /**
   * Constructs the service with the given HTTP client.
   */
  private function createService(ClientInterface $httpClient): ResidentParkingZoneService {
    return new ResidentParkingZoneService(
      $httpClient,
      $this->container->get('language_manager'),
    );
  }

  /**
   * A location inside a zone resolves to a populated DTO with an embed URL.
   */
  public function testReturnsParkingZone(): void {
    $response = new Response(200, [], (string) json_encode([
      'count' => 1,
      'results' => [
        [
          'type' => 'resident_parking_zone',
          'name' => ['fi' => 'Kallio/Sörnäinen'],
          'extra' => ['area_key' => 'F'],
          'boundary' => [
            'type' => 'MultiPolygon',
            'coordinates' => [[[[24.93, 60.17], [24.95, 60.19], [24.94, 60.18]]]],
          ],
        ],
      ],
    ]));

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->with('GET', $this->stringContains('administrative_division'))
      ->willReturn($response);

    $zone = $this->createService($httpClient)
      ->getParkingZone(new Location(60.18, 24.94, 'Point'));

    $this->assertNotNull($zone);
    $this->assertSame('Kallio/Sörnäinen', $zone->name);
    $this->assertStringContainsString('palvelukartta.hel.fi', $zone->embedUrl);
    $this->assertStringContainsString('/embed/area', $zone->embedUrl);
    $this->assertStringContainsString('selected=resident_parking_zone', $zone->embedUrl);
    $this->assertStringContainsString('bbox=', $zone->embedUrl);
    $this->assertSame('F', $zone->areaKey);
    $this->assertStringContainsString('palvelukartta.hel.fi/en/area?', $zone->mapUrl);
    $this->assertStringContainsString('selected=resident_parking_zone', $zone->mapUrl);

    // The bbox is the boundary padded by 3 % on each side.
    $bbox = array_map('floatval', explode(',', $zone->bbox));
    $this->assertCount(4, $bbox);
    foreach ([60.1694, 24.9294, 60.1906, 24.9506] as $i => $expected) {
      $this->assertEqualsWithDelta($expected, $bbox[$i], 0.000001);
    }
  }

  /**
   * A zone without usable geometry resolves to NULL (nothing to show on a map).
   */
  public function testReturnsNullWhenZoneHasNoGeometry(): void {
    $response = new Response(200, [], (string) json_encode([
      'count' => 1,
      'results' => [
        ['type' => 'resident_parking_zone', 'name' => ['fi' => 'Kamppi']],
      ],
    ]));
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($response);

    $zone = $this->createService($httpClient)
      ->getParkingZone(new Location(60.16, 24.93, 'Point'));

    $this->assertNull($zone);
  }

  /**
   * A location outside every zone resolves to NULL.
   */
  public function testReturnsNullWhenNoZone(): void {
    $response = new Response(200, [], (string) json_encode(['count' => 0, 'results' => []]));
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($response);

    $zone = $this->createService($httpClient)
      ->getParkingZone(new Location(60.0, 24.0, 'Point'));

    $this->assertNull($zone);
  }

  /**
   * A failed lookup throws the HTTP client exception.
   */
  public function testThrowsWhenLookupFails(): void {
    $httpClient = $this->createMockHttpClient([
      new ConnectException('Timeout', new Request('GET', 'https://example.com')),
    ]);

    $this->expectException(ConnectException::class);
    $this->createService($httpClient)
      ->getParkingZone(new Location(60.18, 24.94, 'Point'));
  }

  /**
   * Zone names are collected from every page.
   */
  public function testReturnsParkingZoneNames(): void {
    $nextPage = 'https://example.com/next-page';
    $history = [];
    $httpClient = $this->createMockHistoryMiddlewareHttpClient($history, [
      new Response(200, [], (string) json_encode([
        'next' => $nextPage,
        'results' => [
          ['type' => 'resident_parking_zone', 'name' => ['fi' => 'Kamppi']],
          ['type' => 'resident_parking_zone', 'name' => NULL],
        ],
      ])),
      new Response(200, [], (string) json_encode([
        'next' => NULL,
        'results' => [
          ['type' => 'resident_parking_zone', 'name' => ['fi' => 'Kallio/Sörnäinen']],
        ],
      ])),
    ]);

    $names = $this->createService($httpClient)->getParkingZoneNames();

    $this->assertSame(['Kamppi', 'Kallio/Sörnäinen'], $names);
    $this->assertCount(2, $history);
    $this->assertStringContainsString('type=resident_parking_zone', $history[0]['request']->getUri()->getQuery());
    $this->assertEquals($nextPage, (string) $history[1]['request']->getUri(), 'The next page is requested with its own query.');
  }

}

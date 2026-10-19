<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\ParkingZone;

use Drupal\helfi_api_base\ParkingZone\ResidentParkingZoneService;
use Drupal\helfi_api_base\ServiceMap\DTO\Location;
use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;

/**
 * Test parking zone service.
 */
#[RunTestsInSeparateProcesses]
#[Group('helfi_platform_config')]
class ResidentParkingZoneServiceTest extends KernelTestBase {

  /**
   * Constructs the service with the given HTTP client.
   */
  private function createService(ClientInterface $httpClient): ResidentParkingZoneService {
    return new ResidentParkingZoneService(
      $httpClient,
      $this->container->get('language_manager'),
      $this->createMock(LoggerInterface::class),
    );
  }

  /**
   * A location inside a zone resolves to a populated DTO with an embed URL.
   *
   * @covers \Drupal\helfi_api_base\ParkingZone\ResidentParkingZoneService::getParkingZone
   * @covers \Drupal\helfi_api_base\ParkingZone\ResidentParkingZoneService::buildEmbedUrl
   * @covers \Drupal\helfi_api_base\ParkingZone\ResidentParkingZoneService::boundaryToBbox
   * @covers \Drupal\helfi_api_base\ParkingZone\ResidentParkingZoneService::__construct
   */
  public function testReturnsParkingZone(): void {
    $response = new Response(200, [], (string) json_encode([
      'count' => 1,
      'results' => [
        [
          'type' => 'resident_parking_zone',
          'name' => ['fi' => 'Kallio/Sörnäinen'],
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
  }

  /**
   * A zone without usable geometry resolves to NULL (nothing to show on a map).
   *
   * @covers \Drupal\helfi_api_base\ParkingZone\ResidentParkingZoneService::getParkingZone
   * @covers \Drupal\helfi_api_base\ParkingZone\ResidentParkingZoneService::boundaryToBbox
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
   *
   * @covers \Drupal\helfi_api_base\ParkingZone\ResidentParkingZoneService::getParkingZone
   */
  public function testReturnsNullWhenNoZone(): void {
    $response = new Response(200, [], (string) json_encode(['count' => 0, 'results' => []]));
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($response);

    $zone = $this->createService($httpClient)
      ->getParkingZone(new Location(60.0, 24.0, 'Point'));

    $this->assertNull($zone);
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ServiceMap;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\helfi_api_base\ServiceMap\DTO\ParkingZone;
use Drupal\helfi_api_base\ServiceMap\DTO\Location;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

/**
 * Resolves resident parking zones from the Servicemap API.
 *
 * Resident parking zones are administrative divisions in the Servicemap
 * backend.
 */
final class ResidentParkingZoneService implements ResidentParkingZoneServiceInterface {

  private const API_URL = 'https://api.hel.fi/servicemap/v2/administrative_division/';

  private const EMBED_BASE_URL = 'https://palvelukartta.hel.fi';

  private const DIVISION_TYPE = 'resident_parking_zone';

  private const EMBED_LANGUAGES = ['fi', 'sv', 'en'];

  /**
   * Fraction of the zone's size added as padding around the map bounding box.
   */
  private const BBOX_PADDING = 0.03;

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly LanguageManagerInterface $languageManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getParkingZone(Location $location): ?ParkingZone {
    $response = $this->httpClient->request('GET', self::API_URL, [
      RequestOptions::QUERY => [
        'type' => self::DIVISION_TYPE,
        'lat' => $location->lat,
        'lon' => $location->lon,
        'geometry' => 'true',
      ],
      RequestOptions::TIMEOUT => 3,
    ]);

    $result = json_decode((string) $response->getBody(), TRUE)['results'][0] ?? NULL;
    if (!$result) {
      return NULL;
    }

    // No geometry means no map to frame, so treat the zone as absent.
    $bbox = $this->boundaryToBbox($result['boundary']['coordinates'] ?? []);
    if (!$bbox) {
      return NULL;
    }

    return new ParkingZone(
      // The Servicemap API only provides the zone name in Finnish.
      name: (string) ($result['name']['fi'] ?? ''),
      embedUrl: $this->buildMapUrl($location, $bbox, TRUE),
      coordinates: $result['boundary']['coordinates'][0][0],
      areaKey: (string) ($result['extra']['area_key'] ?? ''),
      mapUrl: $this->buildMapUrl($location, $bbox, FALSE),
      bbox: $bbox,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getParkingZoneNames(): array {
    $names = [];
    $url = self::API_URL;
    $options = [
      RequestOptions::QUERY => [
        'type' => self::DIVISION_TYPE,
        'page_size' => 20,
      ],
      RequestOptions::TIMEOUT => 10,
    ];

    while ($url) {
      $response = $this->httpClient->request('GET', $url, $options);
      $data = json_decode((string) $response->getBody(), TRUE);

      foreach ($data['results'] ?? [] as $result) {
        if (!empty($result['name']['fi'])) {
          $names[] = (string) $result['name']['fi'];
        }
      }

      $url = $data['next'] ?? NULL;
      // The next page URL holds the query.
      unset($options[RequestOptions::QUERY]);
    }

    return $names;
  }

  /**
   * Builds the Palvelukartta URL framed to the given bounding box.
   *
   * @param \Drupal\helfi_api_base\ServiceMap\DTO\Location $location
   *   The searched location (map centre and marker).
   * @param string $bbox
   *   Bounding box as "minLat,minLng,maxLat,maxLng".
   * @param bool $embed
   *   TRUE for the embeddable map, FALSE for the full map page.
   *
   * @return string
   *   The Palvelukartta URL.
   */
  private function buildMapUrl(Location $location, string $bbox, bool $embed): string {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $language = in_array($langcode, self::EMBED_LANGUAGES, TRUE) ? $langcode : 'fi';

    return Url::fromUri(
      sprintf('%s/%s/%sarea', self::EMBED_BASE_URL, $language, $embed ? 'embed/' : ''),
      [
        'query' => [
          'selected' => self::DIVISION_TYPE,
          'lat' => $location->lat,
          'lng' => $location->lon,
          'map' => 'servicemap',
          'bbox' => $bbox,
        ],
      ],
    )->toString();
  }

  /**
   * Computes a padded bounding box from GeoJSON coordinates.
   *
   * @param array<int, mixed> $coordinates
   *   GeoJSON coordinates: nested arrays of [lon, lat] positions.
   *
   * @return string|null
   *   Bounding box as "minLat,minLng,maxLat,maxLng", or NULL when there are no
   *   usable coordinates.
   */
  private function boundaryToBbox(array $coordinates): ?string {
    $lons = $lats = [];
    $collect = function (array $node) use (&$collect, &$lons, &$lats): void {
      // A GeoJSON position is a numeric [lon, lat, ...] tuple; anything else is
      // a nested array of positions (ring / polygon / multipolygon).
      if (is_numeric($node[0] ?? NULL) && is_numeric($node[1] ?? NULL)) {
        $lons[] = (float) $node[0];
        $lats[] = (float) $node[1];
        return;
      }
      foreach ($node as $child) {
        if (is_array($child)) {
          $collect($child);
        }
      }
    };
    $collect($coordinates);

    if (!$lons || !$lats) {
      return NULL;
    }

    // Pad so the zone is not flush against the map edges.
    $lonPad = (max($lons) - min($lons)) * self::BBOX_PADDING;
    $latPad = (max($lats) - min($lats)) * self::BBOX_PADDING;

    return implode(',', [
      min($lats) - $latPad,
      min($lons) - $lonPad,
      max($lats) + $latPad,
      max($lons) + $lonPad,
    ]);
  }

}

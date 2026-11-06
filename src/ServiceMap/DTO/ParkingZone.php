<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ServiceMap\DTO;

/**
 * Data transfer object representing a resident parking zone.
 */
final readonly class ParkingZone {

  /**
   * The parking zone dto.
   *
   * @param string $name
   *   The name of the parking zone in finnish.
   * @param string $embedUrl
   *   The url to the embeddable Palvelukartta map.
   * @param array<mixed> $coordinates
   *   The coordinates for the parking zone.
   * @param string $areaKey
   *   The zone identifier letter, for example "J".
   * @param string $mapUrl
   *   The url to the full Palvelukartta map.
   * @param string $bbox
   *   The padded bounding box of the zone as "minLat,minLng,maxLat,maxLng".
   */
  public function __construct(
    public string $name,
    public string $embedUrl = '',
    public array $coordinates = [],
    public string $areaKey = '',
    public string $mapUrl = '',
    public string $bbox = '',
  ) {
  }

}

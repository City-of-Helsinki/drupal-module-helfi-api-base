<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ParkingZone\DTO;

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
   *   The url to the map.
   * @param array<mixed> $coordinates
   *   The coordinates for the parking zone.
   */
  public function __construct(
    public string $name,
    public string $embedUrl,
    public array $coordinates = [],
  ) {
  }

}

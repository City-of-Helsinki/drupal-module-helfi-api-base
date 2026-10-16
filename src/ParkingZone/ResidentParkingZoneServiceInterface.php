<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ParkingZone;

use Drupal\helfi_api_base\ParkingZone\DTO\ParkingZone;
use Drupal\helfi_api_base\ServiceMap\DTO\Location;

/**
 * Resolves the resident parking zone for a geographic location.
 */
interface ResidentParkingZoneServiceInterface {

  /**
   * Gets the resident parking zone containing the given location.
   *
   * @param \Drupal\helfi_api_base\ServiceMap\DTO\Location $location
   *   The location to look up.
   *
   * @return \Drupal\helfi_api_base\ParkingZone\DTO\ParkingZone|null
   *   The parking zone, or NULL when the location has no resident parking zone
   *   or the lookup failed.
   */
  public function getParkingZone(Location $location): ?ParkingZone;

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ParkingZone\DTO;

/**
 * Data transfer object representing a resident parking zone.
 */
final readonly class ParkingZone {

  public function __construct(
    public string $name,
    public string $embedUrl,
  ) {
  }

}

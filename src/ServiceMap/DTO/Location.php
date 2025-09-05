<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ServiceMap\DTO;

/**
 * Data transfer object representing a geographic location.
 *
 * Stores latitude, longitude, and location type, and provides utility
 * methods for creating and formatting location data.
 */
final readonly class Location {

  public function __construct(
    public float $lat,
    public float $lon,
    public string $type,
  ) {
  }

  /**
   * Returns comma separated string of coordinate information.
   *
   * @return string
   *   The coordinate information as a string.
   */
  public function __toString() : string {
    return $this->lat . ', ' . $this->lon;
  }

  /**
   * Creates a Location instance from an associative array.
   *
   * @param array $data
   *   An associative array containing:
   *   - coordinates: An indexed array where the first element is the
   *     longitude and the second is the latitude.
   *   - type: A string describing the location type.
   *
   * @return self
   *   A new Location instance.
   *
   * @throws \InvalidArgumentException
   *   Thrown when either 'coordinates' or 'type' keys are missing.
   */
  public static function createFromArray(array $data) : self {
    if (!isset($data['coordinates'], $data['type'])) {
      throw new \InvalidArgumentException('Missing "coordinates" or "type".');
    }
    [$lon, $lat] = $data['coordinates'];

    return new self((float) $lat, (float) $lon, $data['type']);
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ServiceMap\DTO;

/**
 * Data transfer object representing an address.
 *
 *  Stores street name and location data.
 */
final readonly class Address {

  public function __construct(
    public StreetName $streetName,
    public Location $location,
  ) {
  }

  /**
   * Returns the street name in Finnish as a string.
   *
   * @return string
   *   The Finnish street name.
   */
  public function __toString() : string {
    return $this->streetName->fi;
  }

}

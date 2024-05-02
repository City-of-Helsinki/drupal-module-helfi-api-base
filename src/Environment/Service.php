<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A value object to store service data.
 */
final class Service {

  /**
   * Constructs a new instance.
   */
  public function __construct(
    public readonly ServiceEnum $name,
    public readonly Address $address,
  ) {
  }

}

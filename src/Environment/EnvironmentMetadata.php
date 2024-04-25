<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A value object to store environment metadata.
 */
final class EnvironmentMetadata {

  /**
   * Constructs a new instance.
   */
  private function __construct(
    private readonly array $services,
  ) {
  }

  /**
   * Construct a new instance from array.
   *
   * @param array $data
   *   The data.
   *
   * @return \Drupal\helfi_api_base\Environment\EnvironmentMetadata|null
   *   The self or null.
   */
  public static function createFromArray(array $data) : ? self {
    if (empty($data)) {
      return NULL;
    }

    $required = [
      'services',
    ];

    foreach ($required as $key) {
      if (!isset($data[$key])) {
        throw new \InvalidArgumentException(sprintf('Missing required "%s".', $key));
      }
    }

    $services = [];
    foreach ($data['services'] as $name => $value) {
      if (!isset($value['domain'])) {
        throw new \InvalidArgumentException("$name missing required domain.");
      }
      $services[$name] = new Address(...$value);
    }

    return new self($services);
  }

  /**
   * Gets the services.
   *
   * @return array<string, \Drupal\helfi_api_base\Environment\Address>
   *   The services.
   */
  public function getServices(): array {
    return $this->services;
  }

  /**
   * Gets the given service.
   *
   * @param string $name
   *   The service name.
   *
   * @return \Drupal\helfi_api_base\Environment\Address|null
   *   The service.
   */
  public function getService(string $name) : ?Address {
    return $this->services[$name] ?? NULL;
  }

}

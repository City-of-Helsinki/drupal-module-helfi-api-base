<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Package;

/**
 * A value object to store package version data.
 */
final class Version {

  /**
   * Constructs a new instance.
   *
   * @param string $name
   *   The package name.
   * @param string $latestVersion
   *   The latest version.
   * @param bool $isLatest
   *   Whether the given version is latest or not.
   * @param string $version
   *   The current version.
   */
  public function __construct(
    public string $name,
    public string $latestVersion,
    public bool $isLatest,
    public string $version,
  ) {
  }

  /**
   * Gets the object as an array.
   *
   * @return array
   *   The data as an array.
   */
  public function toArray() : array {
    return [
      'name' => $this->name,
      'latestVersion' => $this->latestVersion,
      'isLatest' => $this->isLatest,
      'version' => $this->version,
    ];
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Package;

/**
 * Defines the version checker interface.
 */
interface VersionCheckerInterface {

  /**
   * Checks whether this collector is applicable.
   *
   * @param string $packageName
   *   The package name to check.
   *
   * @return bool
   *   TRUE if this instance should handle the version check.
   */
  public function applies(string $packageName) : bool;

  /**
   * Gets the version data.
   *
   * @param string $packageName
   *   The package name.
   * @param string $version
   *   The version.
   *
   * @return \Drupal\helfi_api_base\Package\Version|null
   *   The version or null.
   *
   * @throws \Drupal\helfi_api_base\Exception\InvalidPackageException
   */
  public function get(string $packageName, string $version) : ? Version;

}

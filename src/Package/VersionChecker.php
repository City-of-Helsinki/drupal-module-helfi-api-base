<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Package;

/**
 * Provides a package version checker.
 */
final class VersionChecker {

  /**
   * The collectors.
   *
   * @var \Drupal\helfi_api_base\Package\VersionCheckerInterface[]
   */
  private array $collectors = [];

  /**
   * Adds a version checker.
   *
   * @param \Drupal\helfi_api_base\Package\VersionCheckerInterface $versionChecker
   *   The version checker collector.
   *
   * @return $this
   *   The self.
   */
  public function add(VersionCheckerInterface $versionChecker) : self {
    $this->collectors[] = $versionChecker;
    return $this;
  }

  /**
   * Gets the package version.
   *
   * @param string $packageName
   *   The package name.
   * @param string $version
   *   The version.
   *
   * @return \Drupal\helfi_api_base\Package\Version|null
   *   The version object or null.
   *
   * @throws \Drupal\helfi_api_base\Exception\InvalidPackageException
   */
  public function get(string $packageName, string $version) : ? Version {
    foreach ($this->collectors as $collector) {
      if (!$collector->applies($packageName)) {
        continue;
      }
      return $collector->get($packageName, $version);
    }
    return NULL;
  }

}

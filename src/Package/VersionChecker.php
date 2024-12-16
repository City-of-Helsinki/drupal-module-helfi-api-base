<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Package;

use Drupal\helfi_api_base\Exception\VersionCheckException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
   * Cache result of `composer outdated`.
   */
  private array $packages = [];

  /**
   * Constructs a new instance.
   */
  public function __construct(
    #[Autowire(param: 'helfi_api_base.default_composer_lock')]
    private readonly string $defaultComposerLockFile,
    private readonly ComposerOutdatedProcess $process,
  ) {
  }

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

  /**
   * Gets outdated package versions.
   *
   * @param string|null $composerLockFile
   *   Path to composer lock file. Defaults to project lock file.
   *
   * @return Version[]
   *   Outdated packages.
   *
   * @throws \Drupal\helfi_api_base\Exception\VersionCheckException
   */
  public function getOutdated(?string $composerLockFile = NULL) : array {
    $packages = $this->getPackages($composerLockFile ?? $this->defaultComposerLockFile);
    $versions = [];

    foreach ($packages as $packageName => $package) {
      $versions[] = new Version($packageName, $package['latest'], FALSE, $package['version']);
    }

    return $versions;
  }

  /**
   * Get outdated packages.
   *
   * Uses variable cache since running the composer process is expensive.
   *
   * @throws \Drupal\helfi_api_base\Exception\VersionCheckException
   */
  private function getPackages(string $composerLockFile): array {
    if (!$composerLockFile = realpath($composerLockFile)) {
      throw new VersionCheckException('Composer lock file not found');
    }

    $workingDir = dirname($composerLockFile);
    if (empty($this->packages[$workingDir])) {
      try {
        $packages = $this->process->run($workingDir);
        $packages = $packages['installed'] ?? [];
      }
      catch (ProcessFailedException) {
        throw new VersionCheckException("Composer process failed");
      }

      // Key with package name.
      foreach ($packages as $package) {
        $this->packages[$workingDir][$package['name']] = $package;
      }
    }

    return $this->packages[$workingDir] ?? [];
  }

}

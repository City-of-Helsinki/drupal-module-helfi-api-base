<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Package;

use Drupal\helfi_api_base\Exception\InvalidPackageException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provides a version checker for helfi custom modules.
 */
final class HelfiPackage implements VersionCheckerInterface {

  public const BASE_URL = 'https://repository.drupal.hel.ninja/p2/%s.json';

  /**
   * Constructs a new instance.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   */
  public function __construct(
    private ClientInterface $client
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function applies(string $packageName): bool {
    // @todo Allow other city-of-helsinki packages too.
    return str_starts_with($packageName, 'drupal/helfi_')
      || str_starts_with($packageName, 'drupal/hdbt');
  }

  /**
   * Gets the package data.
   *
   * @param string $packageName
   *   The package name.
   * @param bool $dev
   *   Whether to get dev releases or not.
   *
   * @return array
   *   The packages.
   *
   * @throws \Drupal\helfi_api_base\Exception\InvalidPackageException
   */
  private function getPackageData(string $packageName, bool $dev = FALSE) : array {
    $packagePath = $dev ? sprintf('%s~dev', $packageName) : $packageName;

    try {
      $response = $this->client
        ->request('GET', sprintf(self::BASE_URL, $packagePath));
      $content = json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
      return [];
    }
    if (!isset($content['packages'][$packageName])) {
      throw new InvalidPackageException('Package not found.');
    }
    return $content['packages'][$packageName];
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $packageName, string $version): ? Version {
    // Attempt to get package data and fallback to dev version if
    // no stable version is found.
    if (!$packages = $this->getPackageData($packageName)) {
      $packages = $this->getPackageData($packageName, TRUE);
    }
    usort($packages, function ($package1, $package2) {
      return version_compare($package1['version'], $package2['version']);
    });
    // Packages are sorted from oldest to newest.
    $latest = end($packages);

    if (empty($latest['version']) || !is_string($latest['version'])) {
      throw new InvalidPackageException('No version data found.');
    }
    return new Version($packageName, $latest['version'], version_compare($version, $latest['version'], '>='));
  }

}

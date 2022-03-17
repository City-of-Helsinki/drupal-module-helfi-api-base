<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Package;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

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
   * {@inheritdoc}
   */
  public function get(string $packageName, string $version): ? Version {
    try {
      $response = $this->client
        ->request('GET', sprintf(self::BASE_URL, $packageName));
      $content = json_decode($response->getBody()->getContents(), TRUE);

      if (!isset($content['packages'][$packageName])) {
        return NULL;
      }
      $latest = end($content['packages'][$packageName]);

      if (!isset($latest['version']) || !is_string($latest['version'])) {
        return NULL;
      }
      return new Version($packageName, $latest['version'], version_compare($version, $latest['version'], '>='));
    }
    catch (RequestException $e) {
    }
    return NULL;
  }

}

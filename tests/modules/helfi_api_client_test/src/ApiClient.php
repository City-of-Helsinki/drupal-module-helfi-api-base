<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_client_test;

use Drupal\helfi_api_base\ApiClient\ApiClientBase;
use Drupal\helfi_api_base\ApiClient\ApiResponse;
use Drupal\helfi_api_base\ApiClient\CacheValue;

/**
 * Api client for testing.
 */
class ApiClient extends ApiClientBase {

  /**
   * Expose protected method for testing.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function exposedMakeRequest(string $method, string $url, array $options = []): ApiResponse {
    return $this->makeRequest($method, $url, $options);
  }

  /**
   * Expose protected method for testing.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function exposedMakeRequestWithFixture(string $fixture, string $method, string $url, array $options = []): ApiResponse {
    return $this->makeRequestWithFixture($fixture, $method, $url, $options);
  }

  /**
   * Expose protected method for testing.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function exposedCache(string $key, callable $callback): CacheValue {
    return $this->cache($key, $callback);
  }

}

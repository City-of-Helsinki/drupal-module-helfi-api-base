<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_client_test;

use Drupal\helfi_api_base\ApiClient\ApiClientBase;
use Drupal\helfi_api_base\ApiClient\ApiResponse;
use Drupal\helfi_api_base\ApiClient\CacheValue;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class responsible for performing language negotiation.
 */
class ApiClient extends ApiClientBase {

  /**
   * Expose protected method for testing.
   *
   * @throws GuzzleException
   */
  public function exposedMakeRequest(string $method, string $url, array $options = [], ?callable $mockCallback = NULL): ApiResponse {
    return $this->makeRequest($method, $url, $options, $mockCallback);
  }

  /**
   * Expose protected method for testing
   */
  public function exposedCache(string $key, callable $callback): CacheValue {
    return $this->cache($key, $callback);
  }

}

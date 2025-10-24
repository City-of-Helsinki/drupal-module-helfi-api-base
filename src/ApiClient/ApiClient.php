<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ApiClient;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\EnvironmentResolverInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Utils;
use Psr\Log\LoggerInterface;

/**
 * Fetch data from HTTP API.
 *
 * Provides simple caching and fixtures (for local environment).
 */
class ApiClient {

  /**
   * Whether to bypass cache or not.
   *
   * @var bool
   */
  private bool $bypassCache = FALSE;

  /**
   * Whether to use fixtures on failure or not.
   *
   * @var bool
   */
  private bool $useFixtures = FALSE;

  /**
   * The previous exception.
   *
   * @var \Exception|null
   */
  private ?\Exception $previousException = NULL;

  /**
   * Construct an instance.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentResolverInterface $environmentResolver
   *   The environment resolver.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param array $defaultOptions
   *   Default request options.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly CacheBackendInterface $cache,
    protected readonly TimeInterface $time,
    protected readonly EnvironmentResolverInterface $environmentResolver,
    protected readonly LoggerInterface $logger,
    private readonly array $defaultOptions = [],
  ) {
    try {
      $environment = $this->environmentResolver
        ->getActiveEnvironment()
        ->getEnvironment();

      // Use fallback fixtures on local by default.
      $this->useFixtures = $environment === EnvironmentEnum::Local;
    }
    catch (\InvalidArgumentException) {
    }
  }

  /**
   * Allow cache to be bypassed.
   *
   * @return $this
   *   The self.
   */
  public function withBypassCache() : self {
    $instance = clone $this;
    $instance->bypassCache = TRUE;
    return $instance;
  }

  /**
   * Disable fixture fallback.
   *
   * @return $this
   *   The self.
   */
  public function withoutFixtures() : self {
    $instance = clone $this;
    $instance->useFixtures = FALSE;
    return $instance;
  }

  /**
   * Gets the default request options.
   *
   * @param array $options
   *   The optional options.
   *
   * @return array
   *   The request options.
   */
  protected function getRequestOptions(array $options = []) : array {
    // Hardcode cURL options.
    // Curl options are keyed by PHP constants so there is no easy way to
    // define them in yaml files yet. See: https://www.drupal.org/node/3403883
    $default = $this->defaultOptions + [
      'curl' => [CURLOPT_TCP_KEEPALIVE => TRUE],
    ];

    $activeEnvironmentName = $this->environmentResolver
      ->getActiveEnvironment()
      ->getEnvironment();

    if ($activeEnvironmentName === EnvironmentEnum::Local) {
      // Disable SSL verification in local environment.
      $default['verify'] = FALSE;
    }

    return array_merge_recursive($options, $default);
  }

  /**
   * Makes HTTP request.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   The endpoint in the instance.
   * @param array $options
   *   Body for requests.
   *
   * @return \Drupal\helfi_api_base\ApiClient\ApiResponse
   *   The JSON object.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function makeRequest(
    string $method,
    string $url,
    array $options = [],
  ): ApiResponse {

    $options = $this->getRequestOptions($options);

    $response = $this->httpClient->request($method, $url, $options);

    return new ApiResponse(Utils::jsonDecode($response->getBody()->getContents()));
  }

  /**
   * Makes HTTP request with fixture.
   *
   * @param string $fixture
   *   File for mock data if requests fail in local environment.
   * @param string $method
   *   Request method.
   * @param string $url
   *   The endpoint in the instance.
   * @param array $options
   *   Body for requests.
   *
   * @return \Drupal\helfi_api_base\ApiClient\ApiResponse
   *   The JSON object.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function makeRequestWithFixture(
    string $fixture,
    string $method,
    string $url,
    array $options = [],
  ): ApiResponse {
    try {
      if ($this->previousException instanceof \Exception) {
        // Fail any further request instantly after one failed request, so we
        // don't block the rendering process and cause the site to time-out.
        throw $this->previousException;
      }

      return $this->makeRequest($method, $url, $options);
    }
    catch (\Exception $e) {
      if ($e instanceof GuzzleException) {
        $this->previousException = $e;
      }

      // Serve mock data in local environments if requests fail.
      if (
        ($e instanceof ClientException || $e instanceof ConnectException) &&
        $this->useFixtures
      ) {
        $this->logger->warning(
          sprintf('Request failed: %s. Mock data is used instead.', $e->getMessage())
        );

        return ApiFixture::requestFromFile($fixture);
      }

      throw $e;
    }
  }

  /**
   * Gets the cached data for given response.
   *
   * @param string $key
   *   The  cache key.
   * @param callable $callback
   *   The callback to handle requests.
   *
   * @return \Drupal\helfi_api_base\ApiClient\CacheValue|null
   *   The cache or null.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function cache(string $key, callable $callback) : ?CacheValue {
    $exception = new TransferException();
    $value = ($cache = $this->cache->get($key)) ? $cache->data : NULL;

    // Attempt to re-fetch the data in case cache does not exist, cache has
    // expired, or bypass cache is set to true.
    if (
      ($value instanceof CacheValue && $value->hasExpired($this->time->getRequestTime())) ||
      $this->bypassCache ||
      $value === NULL
    ) {
      try {
        $value = $callback();
        $this->cache->set($key, $value, tags: $value->tags);
        return $value;
      }
      catch (GuzzleException $e) {
        // Request callback failed. Catch the exception, so we can still use
        // stale cache if it exists.
        $exception = $e;
      }
    }

    if ($value instanceof CacheValue) {
      return $value;
    }

    // We should only reach this if:
    // 1. Cache does not exist ($value is NULL).
    // 2. API request fails, and we cannot re-populate the cache (caught the
    // exception).
    throw $exception;
  }

  /**
   * Helper method for calculating cache max age.
   *
   * @param int $ttl
   *   Time to live in seconds.
   *
   * @return int
   *   Expires timestamp.
   */
  public function cacheMaxAge(int $ttl): int {
    return $this->time->getRequestTime() + $ttl;
  }

}

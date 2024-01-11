<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\ApiClient;

/**
 * A value object to store cache data.
 */
final readonly class CacheValue {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\ApiClient\ApiResponse $response
   *   The cache data.
   * @param int $expires
   *   The expiration date.
   * @param array $tags
   *   The cache tags.
   */
  public function __construct(
    public ApiResponse $response,
    public int $expires,
    public array $tags,
  ) {
  }

  /**
   * Checks if cache has expired.
   *
   * @param int $currentTime
   *   The current (unix) timestamp.
   *
   * @return bool
   *   TRUE if cache has expired.
   */
  public function hasExpired(int $currentTime) : bool {
    return $currentTime > $this->expires;
  }

}

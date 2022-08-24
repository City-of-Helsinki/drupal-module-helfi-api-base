<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Cache;

/**
 * A helper trait to generate cache keys.
 */
trait CacheKeyTrait {

  /**
   * Converts an array of request options to cache key.
   *
   * @param string $baseKey
   *   The base key.
   * @param array $options
   *   The options.
   *
   * @return string
   *   The flattened cache key.
   */
  protected function requestOptionsToCacheKey(string $baseKey, array $options) : string {
    foreach ($options as $key => $value) {
      // We only care about string keys.
      if (is_string($key)) {
        $baseKey .= sprintf('%s=', $key);
      }
      if (is_array($value)) {
        $baseKey .= $this->requestOptionsToCacheKey('', $value);
      }
      elseif (is_scalar($value)) {
        $baseKey .= $value;
      }
      $baseKey .= ';';
    }
    return rtrim($baseKey, ';');
  }

  /**
   * Gets the cache key for given base key and request options.
   *
   * @param string $baseKey
   *   The base key.
   * @param array $options
   *   The request options.
   *
   * @return string
   *   The cache key.
   */
  protected function getCacheKey(string $baseKey, array $options = []) : string {
    if ($optionsKey = $this->requestOptionsToCacheKey('', $options)) {
      return sprintf('%s:%s', $baseKey, $optionsKey);
    }
    return $baseKey;
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Cache;

/**
 * A service to invalidate cache tags on all instances.
 */
interface CacheTagInvalidatorInterface {

  /**
   * Invalidates given cache tags.
   *
   * @param array $tags
   *   An array of cache tags.
   * @param array $instances
   *   The instances to flush caches from.
   */
  public function invalidateTags(
    array $tags,
    array $instances = [],
  ): void;

}

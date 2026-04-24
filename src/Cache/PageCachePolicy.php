<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Cache;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache policy for responses that have a bubbled max-age=0.
 */
final class PageCachePolicy implements ResponsePolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Response $response, Request $request): ?string {
    if (!$response instanceof CacheableResponseInterface) {
      return NULL;
    }

    if ($response->getCacheableMetadata()->getCacheMaxAge() === 0) {
      // Drupal core sets $build[#cache]['max-age'] = 0 on most 403/404
      // responses because it has no data about cache invalidation.
      // @see https://www.drupal.org/node/2920529
      // But Drupal core still stores these pages in its own cache for a special
      // duration (cache_ttl_4xx setting).
      // @see \Drupal\page_cache\StackMiddleware\PageCache::storeResponse
      // We want to keep this behavior.
      if ($response->isClientError()) {
        return NULL;
      }

      return self::DENY;
    }

    return NULL;
  }

}

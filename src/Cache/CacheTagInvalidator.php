<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Cache;

use Drupal\helfi_api_base\Azure\PubSub\PubSubManager;

/**
 * A service to invalidate cache tags on all instances.
 */
final class CacheTagInvalidator {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Azure\PubSub\PubSubManager $client
   *   The client.
   */
  public function __construct(
    private readonly PubSubManager $client,
  ) {
  }

  /**
   * Invalidates given cache tags.
   *
   * @param array $tags
   *   An array of cache tags.
   *
   * @return $this
   *   The self.
   */
  public function invalidateTags(array $tags) : self {
    $this->client->sendMessage([
      'tags' => $tags,
    ]);
    return $this;
  }

}

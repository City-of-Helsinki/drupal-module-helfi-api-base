<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Cache;

use Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface;
use WebSocket\ConnectionException;

/**
 * A service to invalidate cache tags on all instances.
 */
final class CacheTagInvalidator {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface $pubSubManager
   *   The PubSub manager.
   */
  public function __construct(
    private readonly PubSubManagerInterface $pubSubManager,
  ) {
  }

  /**
   * Invalidates given cache tags.
   *
   * @param array $tags
   *   An array of cache tags.
   * @param array $instances
   *   The instances to flush caches from.
   *
   * @return $this
   *   The self.
   */
  public function invalidateTags(array $tags, array $instances = []) : self {
    try {
      $this->pubSubManager->sendMessage([
        'tags' => $tags,
        'instances' => $instances,
      ]);
    }
    catch (ConnectionException) {
    }
    return $this;
  }

}

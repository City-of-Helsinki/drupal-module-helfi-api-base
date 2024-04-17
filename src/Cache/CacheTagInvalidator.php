<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Cache;

use Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface;
use WebSocket\ConnectionException;

/**
 * A service to invalidate cache tags on all instances.
 */
final class CacheTagInvalidator implements CacheTagInvalidatorInterface {

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
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags, array $instances = []) : void {
    try {
      $this->pubSubManager->sendMessage([
        'tags' => $tags,
        'instances' => $instances,
      ]);
    }
    catch (ConnectionException) {
    }
  }

}

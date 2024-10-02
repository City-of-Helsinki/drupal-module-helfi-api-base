<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use WebSocket\Client;

/**
 * A Web socket client factory.
 */
interface PubSubClientFactoryInterface {

  /**
   * Constructs a new websocket client object.
   *
   * @param string $accessKey
   *   The access key.
   *
   * @return \WebSocket\Client
   *   The client.
   */
  public function create(string $accessKey): Client;

}

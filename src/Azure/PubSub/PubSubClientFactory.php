<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Firebase\JWT\JWT;
use WebSocket\Client;

/**
 * A Web socket client factory.
 */
final class PubSubClientFactory {

  /**
   * Constructs a new websocket client object.
   *
   * @param \Drupal\helfi_api_base\Azure\PubSub\Settings $settings
   *   The settings.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time interface.
   *
   * @return \WebSocket\Client
   *   The client.
   */
  public function create(Settings $settings, TimeInterface $time) : Client {
    $url = sprintf('wss://%s/client/hubs/%s', rtrim($settings->endpoint, '/'), $settings->hub);

    $authorizationToken = JWT::encode([
      'aud' => $url,
      'iat' => $time->getCurrentTime(),
      'exp' => $time->getCurrentTime() + 3600,
      'role' => [
        'webpubsub.sendToGroup',
        'webpubsub.joinLeaveGroup',
      ],
    ], $settings->accessToken, 'HS256');

    return new Client($url, [
      'headers' => [
        'Authorization' => 'Bearer ' . $authorizationToken,
        'Sec-WebSocket-Protocol' => 'json.webpubsub.azure.v1',
      ],
    ]);
  }

}

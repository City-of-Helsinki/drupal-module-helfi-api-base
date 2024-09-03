<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Firebase\JWT\JWT;
use WebSocket\Client;

/**
 * A Web socket client factory.
 */
final class PubSubClientFactory {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time interface.
   * @param \Drupal\helfi_api_base\Azure\PubSub\Settings $settings
   *   The PubSub settings.
   */
  public function __construct(
    private readonly TimeInterface $time,
    private readonly Settings $settings,
  ) {
  }

  /**
   * Constructs a new websocket client object.
   *
   * @param \Drupal\helfi_api_base\Azure\PubSub\AccessTokenType $type
   *   The client type.
   *
   * @return \WebSocket\Client
   *   The client.
   */
  public function create(AccessTokenType $type) : Client {
    $url = sprintf('wss://%s/client/hubs/%s', rtrim($this->settings->endpoint, '/'), $this->settings->hub);

    $accessKey = $this->settings->accessKey;

    if ($type === AccessTokenType::Secondary) {
      $accessKey = $this->settings->secondaryAccessKey;
    }
    $authorizationToken = JWT::encode([
      'aud' => $url,
      'iat' => $this->time->getCurrentTime(),
      'exp' => $this->time->getCurrentTime() + 3600,
      'role' => [
        'webpubsub.sendToGroup',
        'webpubsub.joinLeaveGroup',
      ],
    ], $accessKey, 'HS256');

    return new Client($url, [
      'headers' => [
        'Authorization' => 'Bearer ' . $authorizationToken,
        'Sec-WebSocket-Protocol' => 'json.webpubsub.azure.v1',
      ],
    ]);
  }

}

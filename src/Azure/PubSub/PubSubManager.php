<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebSocket\Client;

/**
 * A client to interact with Azure's PubSub service.
 */
final class PubSubManager implements PubSubManagerInterface {

  /**
   * Constructs a new instance.
   *
   * @param \WebSocket\Client $client
   *   The websocket client.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The datetime service.
   * @param \Drupal\helfi_api_base\Azure\PubSub\Settings $settings
   *   The PubSub settings.
   */
  public function __construct(
    private readonly Client $client,
    private readonly EventDispatcherInterface $eventDispatcher,
    private readonly TimeInterface $time,
    private readonly Settings $settings,
  ) {
  }

  /**
   * Joins to a group.
   *
   * @return self
   *   The self.
   *
   * @throws \JsonException
   * @throws \WebSocket\ConnectionException
   * @throws \WebSocket\TimeoutException
   */
  private function joinGroup() : self {
    $this->client->text(
      $this->encodeMessage([
        'type' => 'joinGroup',
        'group' => $this->settings->group,
      ])
    );
    return $this;
  }

  /**
   * Encodes the message.
   *
   * @param array $data
   *   The data to encode.
   *
   * @return string
   *   The encoded message.
   *
   * @throws \JsonException
   */
  private function encodeMessage(array $data) : string {
    return json_encode($data, JSON_THROW_ON_ERROR);
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) : self {
    static $group = NULL;

    // Join group only once per session.
    if ($group === NULL) {
      $group = $this->joinGroup();
    }
    $this->client
      ->text(
        $this->encodeMessage([
          'type' => 'sendToGroup',
          'group' => $this->settings->group,
          'dataType' => 'json',
          'data' => $message + [
            'timestamp' => $this->time->getCurrentTime(),
          ],
        ])
      );

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function receive() : string {
    static $group = NULL;

    // Join group only once per session.
    if ($group === NULL) {
      $group = $this->joinGroup();
    }
    $message = $this->client->receive();
    $json = json_decode($message, TRUE, flags: JSON_THROW_ON_ERROR);

    $this->eventDispatcher
      ->dispatch(new PubSubMessage($json));
    return $message;
  }

}

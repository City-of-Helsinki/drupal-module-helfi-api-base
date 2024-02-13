<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebSocket\Client;
use WebSocket\ConnectionException;

/**
 * A client to interact with Azure's PubSub service.
 */
final class PubSubManager implements PubSubManagerInterface {

  /**
   * A flag indicating whether we've joined the group.
   *
   * @var bool
   */
  private bool $joinedGroup = FALSE;

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
   * Joins a group.
   *
   * @throws \JsonException
   * @throws \WebSocket\ConnectionException
   * @throws \WebSocket\TimeoutException
   */
  private function joinGroup() : void {
    if ($this->joinedGroup) {
      return;
    }
    $this->client->text(
      $this->encodeMessage([
        'type' => 'joinGroup',
        'group' => $this->settings->group,
      ])
    );

    try {
      // Wait until we've actually joined the group.
      $message = $this->decodeMessage((string) $this->client->receive());

      if (isset($message['event']) && $message['event'] === 'connected') {
        $this->joinedGroup = TRUE;

        return;
      }
    }
    catch (\JsonException) {
    }

    throw new ConnectionException('Failed to join a group.');
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
   * Decodes the received message.
   *
   * @param string $message
   *   The message to decode.
   *
   * @return array
   *   The decoded message.
   *
   * @throws \JsonException
   */
  private function decodeMessage(string $message) : array {
    return json_decode($message, TRUE, flags: JSON_THROW_ON_ERROR);
  }

  /**
   * {@inheritdoc}
   */
  public function setTimeout(int $timeout) : self {
    $this->client->setTimeout($timeout);
    return $this;
  }

  /**
   * Asserts the settings.
   *
   * This is used to exit early if required settings are not populated.
   */
  private function assertSettings() : void {
    $vars = get_object_vars($this->settings);

    foreach ($vars as $key => $value) {
      if (empty($this->settings->{$key})) {
        throw new ConnectionException("Azure PubSub '$key' is not configured.");
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) : self {
    $this->assertSettings();
    $this->joinGroup();

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
    $this->assertSettings();
    $this->joinGroup();

    $message = (string) $this->client->receive();
    $json = $this->decodeMessage($message);

    $this->eventDispatcher
      ->dispatch(new PubSubMessage($json));
    return $message;
  }

}

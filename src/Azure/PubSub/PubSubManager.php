<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Utility\Error;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebSocket\Client;
use WebSocket\ConnectionException;

/**
 * A client to interact with Azure's PubSub service.
 */
final class PubSubManager implements PubSubManagerInterface {

  /**
   * The websocket client.
   *
   * @var \WebSocket\Client|null
   */
  private ?Client $client = NULL;

  /**
   * A flag indicating whether we've joined the group.
   *
   * @var bool
   */
  protected bool $joinedGroup = FALSE;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Azure\PubSub\PubSubClientFactoryInterface $clientFactory
   *   The client factory.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The datetime service.
   * @param \Drupal\helfi_api_base\Azure\PubSub\Settings $settings
   *   The PubSub settings.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(
    private readonly PubSubClientFactoryInterface $clientFactory,
    private readonly EventDispatcherInterface $eventDispatcher,
    private readonly TimeInterface $time,
    private readonly Settings $settings,
    #[Autowire(service: 'logger.channel.helfi_api_base')] private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Receives a message from PubSub service.
   *
   * @return string
   *   The received message.
   *
   * @throws \WebSocket\ConnectionException
   */
  private function clientReceive() : string {
    if ($this->client) {
      return (string) $this->client->receive();
    }
    $exception = new ConnectionException('Failed to receive message.');

    // Initialize client with primary key, fallback to secondary key.
    foreach ($this->settings->accessKeys as $key) {
      try {
        $client = $this->clientFactory->create($key);
        $message = (string) $client->receive();

        $this->client = $client;

        return $message;
      }
      catch (ConnectionException $exception) {
        Error::logException($this->logger, $exception);
      }
    }
    throw $exception;
  }

  /**
   * Sends a text message to PubSub service.
   *
   * @param array $message
   *   The message to send.
   *
   * @throws \JsonException
   * @throws \WebSocket\ConnectionException
   */
  private function clientText(array $message) : void {
    $message = $this->encodeMessage($message);

    if ($this->client) {
      $this->client->text($message);

      return;
    }
    $exception = new ConnectionException('Failed to send text.');

    // Initialize client with primary key, fallback to secondary key.
    foreach ($this->settings->accessKeys as $key) {
      try {
        $client = $this->clientFactory->create($key);
        $client->text($message);
        $this->client = $client;
        return;
      }
      catch (ConnectionException $exception) {
        Error::logException($this->logger, $exception);
      }
    }
    throw $exception;
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
    $this->clientText([
      'type' => 'joinGroup',
      'group' => $this->settings->group,
    ]);

    try {
      // Wait until we've actually joined the group.
      $message = $this->decodeMessage($this->clientReceive());

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
  public function sendMessage(array $message) : self {
    $this->joinGroup();

    $this
      ->clientText([
        'type' => 'sendToGroup',
        'group' => $this->settings->group,
        'dataType' => 'json',
        'data' => $message + [
          'timestamp' => $this->time->getCurrentTime(),
        ],
      ]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function receive() : string {
    $this->joinGroup();

    $message = $this->clientReceive();
    $json = $this->decodeMessage($message);

    $this->eventDispatcher
      ->dispatch(new PubSubMessage($json));
    return $message;
  }

}

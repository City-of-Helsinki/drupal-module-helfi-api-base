<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Utility\Error;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebSocket\Client;
use WebSocket\Exception\Exception;
use WebSocket\Message\Ping;

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
   * Initializes the websocket client.
   *
   * @throws \JsonException
   */
  private function initializeClient() : void {
    if ($this->client) {
      return;
    }
    $client = $exception = NULL;

    if (empty($this->settings->accessKeys)) {
      throw new \LogicException('PubSub access key is undefined.');
    }
    // Initialize client with primary key, fallback to secondary key.
    foreach ($this->settings->accessKeys as $key) {
      $exception = NULL;

      try {
        $client = $this->clientFactory->create($key);
        $client->text($this->encodeMessage([
          'type' => 'joinGroup',
          'group' => $this->settings->group,
        ]));
      }
      catch (Exception $exception) {
        Error::logException($this->logger, $exception, level: LogLevel::INFO);
      }
    }

    // Propagate the error if connection fails with all available access keys.
    // When this is called from the Drush command, this causes the command to
    // fail with exit code 1.
    if ($exception instanceof Exception) {
      throw $exception;
    }

    try {
      // Wait until we've actually joined the group.
      $message = $this->decodeMessage($client->receive()->getContent());

      if (isset($message['event']) && $message['event'] === 'connected') {
        $this->client = $client;

        return;
      }
    }
    catch (\JsonException) {
    }
    throw new \LogicException('Failed to initialize the client.');
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
    if ($message === '') {
      return [];
    }
    return json_decode($message, TRUE, flags: JSON_THROW_ON_ERROR);
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) : self {
    $this->initializeClient();

    $this->client->text($this->encodeMessage([
      'type' => 'sendToGroup',
      'group' => $this->settings->group,
      'dataType' => 'json',
      'data' => $message + [
        'timestamp' => $this->time->getCurrentTime(),
      ],
    ]));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function receive() : string {
    $this->initializeClient();

    // Listen until we receive a non-ping message.
    do {
      $message = $this->client->receive();

      if (!$message instanceof Ping) {
        break;
      }
    } while (TRUE);

    $json = $this->decodeMessage($message->getContent());

    $this->eventDispatcher
      ->dispatch(new PubSubMessage($json));
    return $message->getContent();
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Azure\PubSub;

/**
 * A client to interact with Azure's PubSub service.
 */
interface PubSubManagerInterface {

  /**
   * Sends a message to a group.
   *
   * @param array $message
   *   The message to send.
   *
   * @return $this
   *   The self.
   *
   * @throws \JsonException
   */
  public function sendMessage(array $message): self;

  /**
   * Receive messages from given hub and group.
   *
   * @return string
   *   The received message.
   *
   * @throws \JsonException
   * @throws \WebSocket\ConnectionException
   * @throws \WebSocket\TimeoutException
   */
  public function receive(): string;

}

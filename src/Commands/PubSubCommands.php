<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface;
use Drush\Attributes\Command;
use Drush\Commands\DrushCommands;
use WebSocket\TimeoutException;

/**
 * A drush command to process PubSub events from Azure.
 *
 * Usage:
 *
 * $ drush helfi:azure-pubsub-listen
 *    This will listen to and process messages until the MAX_MESSAGES is
 *    reached and then exits with code 0.
 */
final class PubSubCommands extends DrushCommands {

  public const MAX_MESSAGES = 500;
  public const CLIENT_TIMEOUT = 120;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface $pubSubClient
   *   The PubSub client.
   */
  public function __construct(
    private readonly PubSubManagerInterface $pubSubClient,
  ) {
  }

  /**
   * Listen to PubSub messages.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:azure:pubsub-listen')]
  public function listen() : int {
    $this->pubSubClient->setTimeout(self::CLIENT_TIMEOUT);

    for ($received = 0; $received < self::MAX_MESSAGES; $received++) {
      try {
        $message = $this->pubSubClient->receive();
        $this->io()
          ->writeln(sprintf('Received message [#%d]: %s', $received, $message));
      }
      catch (TimeoutException) {
      }
      catch (\JsonException $e) {
        $this->io()->writeln('Invalid json: ' . $e->getMessage());
      }
    }
    $this->io()
      ->writeln(sprintf('Received maximum number of messages (%s). Exiting...', self::MAX_MESSAGES));

    return DrushCommands::EXIT_SUCCESS;
  }

}

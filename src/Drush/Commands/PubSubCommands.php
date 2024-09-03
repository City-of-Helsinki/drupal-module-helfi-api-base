<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Drush\Commands;

use Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface;
use Drush\Attributes\Command;
use Drush\Commands\AutowireTrait;
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

  use AutowireTrait;

  public const MAX_MESSAGES = 100;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface $pubSubManager
   *   The PubSub client.
   */
  public function __construct(
    private readonly PubSubManagerInterface $pubSubManager,
  ) {
    parent::__construct();
  }

  /**
   * Listen to PubSub messages.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:azure:pubsub-listen')]
  public function listen() : int {
    for ($received = 0; $received < self::MAX_MESSAGES; $received++) {
      try {
        $message = $this->pubSubManager->receive();
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

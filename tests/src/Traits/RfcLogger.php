<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * Provides an RFC logger to catch logged messages.
 */
class RfcLogger implements LoggerInterface {

  use RfcLoggerTrait;

  /**
   * The messages array.
   *
   * @var array
   */
  public array $messages = [];

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) : void {
    // We use this same method to test messages from logger channel and
    // watchdog_exception(). The message logged by watchdog_exception()
    // is just a placeholder without any information.
    $entry = [
      'message' => $context['@message'] ?? $message,
      'type' => $context['%type'] ?? NULL,
    ];
    $this->messages[] = $entry;
  }

}

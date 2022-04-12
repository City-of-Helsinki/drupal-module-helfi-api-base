<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Logger;

use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * Provides an RFC logger to catch logged messages.
 */
class RfcTestLogger implements LoggerInterface {

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
    // defaults to a placeholder message. Always default to @message from
    // context if available.
    $entry = [
      'message' => $context['@message'] ?? $message,
      'type' => $context['%type'] ?? NULL,
    ];
    $this->messages[] = $entry;
  }

}

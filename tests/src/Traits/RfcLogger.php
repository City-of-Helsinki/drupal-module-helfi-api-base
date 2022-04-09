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
    $this->messages[] = $context;
  }

}

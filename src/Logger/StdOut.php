<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Logger;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\Core\Logger\RfcLogLevel;
use Psr\Log\LoggerInterface;

/**
 * Provides a 'stdout' logger.
 */
final class StdOut implements LoggerInterface {

  use RfcLoggerTrait;
  use DependencySerializationTrait;

  /**
   * Gets the log message.
   *
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The log message parser.
   */
  public function __construct(private LogMessageParserInterface $parser) {
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) : void {
    global $base_url;

    $file = $level <= RfcLogLevel::WARNING ? 'php://stderr' : 'php://stdout';
    $stream = fopen($file, 'w');

    // Populate the message placeholders and then replace them in the message.
    $variables = $this->parser->parseMessagePlaceholders($message, $context);
    $message = empty($variables) ? $message : strtr($message, $variables);

    $entry = strtr('!base_url|!timestamp|!type|!ip|!request_uri|!referer|!uid|!link|!message', [
      '!base_url' => $base_url,
      '!timestamp' => $context['timestamp'],
      '!type' => $context['channel'],
      '!ip' => $context['ip'],
      '!request_uri' => $context['request_uri'],
      '!referer' => $context['referer'],
      '!severity' => $level,
      '!uid' => $context['uid'],
      '!link' => strip_tags($context['link']),
      '!message' => strip_tags($message),
    ]);

    fwrite($stream, $entry . "\r\n");
    fclose($stream);
  }

}

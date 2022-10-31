<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Logger;

use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Logger\LogMessageParserInterface;

/**
 * This class allows logging to stdout and stderr.
 */
final class JsonLog implements LoggerInterface {

  use RfcLoggerTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   * @param string $stream
   *   The output path.
   * @param bool $loggerEnabled
   *   Whether logger is enabled or not.
   */
  public function __construct(
    private LogMessageParserInterface $parser,
    private string $stream,
    private bool $loggerEnabled = TRUE
  ) {
  }

  /**
   * Outputs the given entry.
   *
   * @param array $entry
   *   The log entry.
   */
  private function output(array $entry) : void {
    $stream = fopen($this->stream, 'a');
    fwrite($stream, json_encode(['message' => $entry]) . PHP_EOL);
    fclose($stream);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) : void {
    if (!$this->loggerEnabled) {
      return;
    }
    global $base_url;
    $severity = RfcLogLevel::getLevels()[$level];

    if ($severity instanceof TranslatableMarkup) {
      $severity = strtolower($severity->getUntranslatedString());
    }
    // Populate the message placeholders and then replace them in the message.
    $variables = $this->parser->parseMessagePlaceholders($message, $context);
    $message = empty($variables) ? $message : strtr($message, $variables);

    $this->output([
      'base_url'    => $base_url,
      'timestamp'   => $context['timestamp'],
      'severity'    => $severity,
      'type'        => $context['channel'],
      'message'     => $message,
      'uid'         => $context['uid'] ?? 0,
      'request_uri' => $context['request_uri'] ?? NULL,
      'referer'     => $context['referer'] ?? NULL,
      'ip'          => $context['ip'] ?? NULL,
      'link'        => (string) ($context['link'] ?? NULL),
      'date'        => date(\DateTimeInterface::ATOM, $context['timestamp']),
    ]);
  }

}

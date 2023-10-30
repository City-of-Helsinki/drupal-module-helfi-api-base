<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Logger;

use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

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
   * @param \Symfony\Component\Filesystem\Filesystem $filesystem
   *   The file system.
   * @param string $stream
   *   The output path.
   * @param bool $loggerEnabled
   *   Whether logger is enabled or not.
   */
  public function __construct(
    private readonly LogMessageParserInterface $parser,
    private readonly Filesystem $filesystem,
    private readonly string $stream,
    private readonly bool $loggerEnabled = TRUE
  ) {
  }

  /**
   * Outputs the messages.
   */
  private function output(array $message) : void {
    $this->filesystem
      ->appendToFile($this->stream, json_encode(['message' => $message]) . PHP_EOL);
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
    $message = empty($variables) ? $message : strtr((string) $message, $variables);

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

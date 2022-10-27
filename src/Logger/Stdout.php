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
final class Stdout implements LoggerInterface {

  use RfcLoggerTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   * @param bool $enableCliLogging
   *   Whether to log messages when running via CLI.
   */
  public function __construct(
    private LogMessageParserInterface $parser,
    private bool $enableCliLogging = FALSE
  ) {
  }

  /**
   * Outputs the given entry.
   *
   * @param string $output
   *   The output.
   * @param string $entry
   *   The log entry.
   */
  private function output(string $output, string $entry) : void {
    // Use php://output stream when dealing with CLI.
    // PHPUnit uses php://stdout by default, and will cause tests to fail
    // because it doesn't know how to differentiate log entries from normal
    // output, thus we cannot the output with ::expectOutputString().
    if (php_sapi_name() === 'cli') {
      // Never output anything unless logging is enabled explicitly.
      // This can be enabled by setting `helfi_api_base.enable_cli_logging`
      // service container parameter to true.
      if (!$this->enableCliLogging) {
        return;
      }
      $output = 'php://output';
    }
    $stream = fopen($output, 'w');
    fwrite($stream, $entry . "\r\n");
    fclose($stream);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) : void {
    global $base_url;
    $severity = RfcLogLevel::getLevels()[$level];

    if ($severity instanceof TranslatableMarkup) {
      $severity = strtoupper($severity->getUntranslatedString());
    }
    // Populate the message placeholders and then replace them in the message.
    $variables = $this->parser->parseMessagePlaceholders($message, $context);
    $message = empty($variables) ? $message : strtr($message, $variables);

    $fmt = '[@severity] [@type] [@date] @message | uid: @uid | request-uri: @request_uri | refer: @referer | ip:  @ip | link: @link';

    $entry = strtr($fmt, [
      '@base_url'    => $base_url,
      '@timestamp'   => $context['timestamp'],
      '@severity'    => $severity,
      '@type'        => $context['channel'],
      '@message'     => strip_tags($message),
      '@uid'         => $context['uid'],
      '@request_uri' => $context['request_uri'],
      '@referer'     => $context['referer'],
      '@ip'          => $context['ip'],
      '@link'        => strip_tags($context['link']),
      '@date'        => date('Y-m-d\TH:i:s', $context['timestamp']),
    ]);
    $output = $level <= RfcLogLevel::WARNING ? 'php://stderr' : 'php://stdout';

    $this->output($output, $entry);
  }

}

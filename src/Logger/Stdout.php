<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Logger;

use Drupal\Core\Logger\RfcLoggerTrait;
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
   */
  public function __construct(private LogMessageParserInterface $parser) {
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) : void {
    if (php_sapi_name() === 'cli') {
      return;
    }
    global $base_url;

    $output = $level <= RfcLogLevel::WARNING ? 'php://stderr' : 'php://stdout';
    $output = fopen($output, 'w');

    $severity = strtoupper((string) RfcLogLevel::getLevels()[$level]);

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

    fwrite($output, $entry . "\r\n");
    fclose($output);
  }

}

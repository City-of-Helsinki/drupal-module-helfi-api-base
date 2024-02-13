<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\helfi_api_base\Logger\JsonLog;
use Drupal\KernelTests\KernelTestBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests json logger.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Logger\JsonLog
 * @group helfi_api_base
 */
class JsonLoggerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) : void {
    parent::register($container);

    $container->setParameter('helfi_api_base.json_logger_path', 'public://drupal.log');
  }

  /**
   * Gets the logger.
   *
   * @param bool $status
   *   Whether logging is enabled or not.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger.
   */
  private function getSut(bool $status) :  LoggerInterface {
    return new JsonLog(
      $this->container->get('logger.log_message_parser'),
      new Filesystem(),
      $this->container->getParameter('helfi_api_base.json_logger_path'),
      $status
    );
  }

  /**
   * Perform assertions against log entries.
   *
   * @param string $message
   *   The message.
   * @param string $type
   *   The type.
   */
  private function assertLogMessage(string $message, string $type) : void {
    $loggerEntry = json_decode(file_get_contents('public://drupal.log'));
    $this->assertEquals($message, $loggerEntry->message->message);
    $this->assertEquals($type, $loggerEntry->message->type);
  }

  /**
   * Tests that logging is enabled by default.
   *
   * @covers ::log
   * @covers ::output
   * @covers ::__construct
   */
  public function testLog() : void {
    \Drupal::logger('helfi_api_base')->warning('Test');
    $this->assertLogMessage('Test', 'helfi_api_base');
  }

  /**
   * Make sure nothing is logged when logger is disabled.
   *
   * @covers ::log
   * @covers ::__construct
   * @covers ::output
   */
  public function testLoggingStatus() : void {
    $this->getSut(FALSE)->warning('Test');
    $this->assertFalse(file_exists('public://drupal.log'));
    $this->getSut(TRUE)->warning('Test', [
      'timestamp' => time(),
      'channel' => 'helfi_api_base',
    ]);
    $this->assertLogMessage('Test', 'helfi_api_base');
  }

}

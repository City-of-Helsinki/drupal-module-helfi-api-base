<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;

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
   * @covers ::log
   * @covers ::output
   */
  public function testLog() : void {
    \Drupal::logger('helfi_api_base')->warning('Test');
    $loggerEntry = json_decode(file_get_contents('public://drupal.log'));
    $this->assertEquals('Test', $loggerEntry->message->message);
    $this->assertEquals('helfi_api_base', $loggerEntry->message->type);
  }

}

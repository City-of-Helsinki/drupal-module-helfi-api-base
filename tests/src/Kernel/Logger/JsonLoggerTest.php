<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\helfi_api_base\Features\FeatureManager;
use Drupal\helfi_api_base\Logger\JsonLog;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests json logger.
 *
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
   * Make sure nothing is logged when logger is disabled.
   */
  public function testLoggingDisabled() : void {
    $features = $this->container->get(FeatureManager::class);
    $features->disableFeature(FeatureManager::LOGGER);
    /** @var \Drupal\helfi_api_base\Logger\JsonLog $sut */
    $sut = $this->container->get(JsonLog::class);
    $sut->warning('Test');
    $this->assertFalse(file_exists('public://drupal.log'));
  }

  /**
   * Make sure messages are logged when the logger is enabled.
   */
  public function testLoggingEnabled() : void {
    $features = $this->container->get(FeatureManager::class);
    $features->enableFeature(FeatureManager::LOGGER);

    /** @var \Drupal\helfi_api_base\Logger\JsonLog $sut */
    $sut = $this->container->get(JsonLog::class);
    $sut->warning('Test', [
      'timestamp' => time(),
      'channel' => 'helfi_api_base',
    ]);
    $this->assertLogMessage('Test', 'helfi_api_base');
    $this->assertTrue(file_exists('public://drupal.log'));
    $this->assertLogMessage('Test', 'helfi_api_base');
  }

}

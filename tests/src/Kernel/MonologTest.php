<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\monolog\Logger\ConditionResolver\ConditionResolverInterface;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests monolog configuration.
 */
final class MonologTest extends KernelTestBase {

  use ProphecyTrait;

  /**
   * Log level that is configured for the duration of this test.
   */
  private const TEST_LOG_LEVEL = 'warning';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'monolog',
    'helfi_api_base',
  ];

  /**
   * Temporary file where logs are stored.
   *
   * @var string
   */
  private string $logFile;

  /**
   * {@inheritdoc}
   */
  protected function bootKernel() : void {
    // Set test log level before kernel is booted.
    $this->setSetting('helfi_api_base.log_level', self::TEST_LOG_LEVEL);

    parent::bootKernel();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->logFile = 'temporary://' . $this->randomMachineName();

    $this->container
      ->getDefinition('monolog.handler.website')
      ->replaceArgument(0, $this->logFile);

    // Pretend that we are running in web process so Drush log is not used.
    $this->container->set('monolog.condition_resolver.cli', new class implements ConditionResolverInterface {

      /**
       * {@inheritdoc}
       */
      public function resolve(): bool {
        return FALSE;
      }

    });
  }

  /**
   * Tests logger message.
   */
  public function testLogging() : void {
    /** @var \Psr\Log\LoggerInterface $logger */
    $logger = $this->container->get('logger.channel.default');
    $logger->warning('Test warning message');
    $logger->debug('Test debug message');

    $log = file_get_contents($this->logFile);
    $this->assertNotFalse($log);

    // Debug messages were not logged due to the log level.
    $this->assertStringContainsString('Test warning message', $log);
    $this->assertStringNotContainsString('Test debug message', $log);

    foreach (explode('\n', $log) as $logLine) {
      // Message is valid JSON.
      $message = json_decode($logLine, flags: JSON_THROW_ON_ERROR);

      // Tests \Drupal\helfi_api_base\Logger\CurrentUserProcessor.
      $this->assertObjectNotHasProperty('user', $message->extra);
    }
  }

}

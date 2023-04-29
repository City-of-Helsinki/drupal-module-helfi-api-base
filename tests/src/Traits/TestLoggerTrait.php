<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\helfi_api_base\Logger\RfcTestLogger;
use Psr\Log\LoggerInterface;

/**
 * Provides a test logger trait to catch log messages.
 */
trait TestLoggerTrait {

  /**
   * The test logger.
   *
   * @var null|\Psr\Log\LoggerInterface
   */
  protected ?LoggerInterface $testLogger = NULL;

  /**
   * The expected log messages.
   *
   * @var null|array
   */
  protected ?array $expectedLogMessages = [];

  /**
   * Setups the mock logger.
   */
  protected function setUpMockLogger() : void {
    $this->testLogger = new RfcTestLogger();
    $this->container->get('logger.factory')->addLogger($this->testLogger);
  }

  /**
   * {@inheritdoc}
   */
  protected function assertPostConditions(): void {
    $messages = $this->testLogger?->messages;
    if (!empty($this->expectedLogMessages)) {
      foreach ($this->expectedLogMessages as $item) {
        ['message' => $expectedMessage, 'type' => $expectedType] = $item;

        $key = array_search($expectedMessage, array_column($messages, 'message'));

        if (!isset($messages[$key])) {
          $this->fail(sprintf('No message %s found.', $expectedMessage));
        }
        $message = $messages[$key];

        $this->assertEquals($expectedMessage, $message['message']);
        $this->assertEquals($expectedType, $message['type']);
        unset($messages[$key]);
      }

      // Make sure we have no messages left.
      $this->assertCount(0, $messages);
    }
    elseif ($messages) {
      $this->assertEmpty($messages);
    }

    parent::assertPostConditions();
  }

  /**
   * Sets the expected log message.
   */
  public function expectLogMessage(string $message, ?string $type = NULL): void {
    $this->expectedLogMessages[] = [
      'message' => $message,
      'type' => $type,
    ];
  }

}

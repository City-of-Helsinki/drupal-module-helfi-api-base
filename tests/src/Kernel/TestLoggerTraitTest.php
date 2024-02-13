<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\Utility\Error;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\TestLoggerTrait;

/**
 * Tests test logger trait.
 *
 * @todo Remove once #2903456 lands in core
 *
 * @group helfi_api_base
 */
class TestLoggerTraitTest extends KernelTestBase {

  use TestLoggerTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();
    $this->setUpMockLogger();
  }

  /**
   * Tests expected logger message.
   */
  public function testExpectsLogEntry() : void {
    $this->expectLogMessage('Test message');

    /** @var \Psr\Log\LoggerInterface $logger */
    $logger = $this->container->get('logger.channel.default');
    $logger->notice('Test message');
  }

  /**
   * Make sure tests without an expected log message works.
   */
  public function testDoesNotExpectLogEntry() : void {
    $this->expectNotToPerformAssertions();
  }

  /**
   * Tests watchdog_exception().
   */
  public function testWatchdogException() : void {
    $this->expectLogMessage('Test message', \InvalidArgumentException::class);
    Error::logException($this->testLogger, new \InvalidArgumentException('Test message'));
  }

}

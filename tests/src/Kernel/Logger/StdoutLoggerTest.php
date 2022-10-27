<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests stdout logger.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Logger\Stdout
 * @group helfi_api_base
 */
class StdoutLoggerTest extends KernelTestBase {

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

    $container->setParameter('helfi_api_base.enable_cli_logging', TRUE);
  }

  /**
   * @covers ::log
   * @covers ::output
   */
  public function testLog() : void {
    $this->expectOutputRegex("/\[WARNING\] \[helfi_api_base\] \[(.*)\] Test (.*)/");
    \Drupal::logger('helfi_api_base')->warning('Test');
  }

}

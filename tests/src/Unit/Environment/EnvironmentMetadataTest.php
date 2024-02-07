<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\helfi_api_base\Environment\EnvironmentMetadata;
use Drupal\helfi_api_base\Exception\EnvironmentException;
use Drupal\Tests\UnitTestCase;

/**
 * Tests EnvironmentMetadata value object.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\EnvironmentMetadata
 * @group helfi_api_base
 */
class EnvironmentMetadataTest extends UnitTestCase {

  /**
   * @covers ::createFromArray
   */
  public function testRequiredValueException() : void {
    $caught = FALSE;
    try {
      EnvironmentMetadata::createFromArray(['nonexistent' => 'dsa']);
    }
    catch (EnvironmentException $e) {
      $this->assertMatchesRegularExpression('/Missing required/', $e->getMessage());
      $caught = TRUE;
    }
    $this->assertTrue($caught);
  }

  /**
   * @covers ::createFromArray
   */
  public function testNull() : void {
    $sut = EnvironmentMetadata::createFromArray([]);
    $this->assertNull($sut);
  }

  /**
   * @covers ::__construct
   * @covers ::createFromArray
   * @covers ::getOpenshiftConsoleLink
   */
  public function testGetters() : void {
    $sut = EnvironmentMetadata::createFromArray([
      'openshift_console_link' => 'https://example.com',
    ]);
    $this->assertEquals('https://example.com', $sut->getOpenshiftConsoleLink());
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\helfi_api_base\Environment\EnvironmentMetadata;
use Drupal\Tests\UnitTestCase;

/**
 * Tests environment resolver value objects.
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
    catch (\InvalidArgumentException $e) {
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

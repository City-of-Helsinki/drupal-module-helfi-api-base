<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\helfi_api_base\Environment\ProjectMetadata;
use Drupal\Tests\UnitTestCase;

/**
 * Tests Project metadata value object.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\ProjectMetadata
 * @group helfi_api_base
 */
class ProjectMetadataTest extends UnitTestCase {

  /**
   * @covers ::createFromArray
   */
  public function testRequiredValueException() : void {
    $caught = FALSE;
    try {
      ProjectMetadata::createFromArray(['repository' => 'test']);
    }
    catch (\InvalidArgumentException $e) {
      $this->assertMatchesRegularExpression('/Missing required/', $e->getMessage());
      $caught = TRUE;
    }
    $this->assertTrue($caught);
  }

  /**
   * @covers ::__construct
   * @covers ::createFromArray
   * @covers ::getAzureDevopsLink
   * @covers ::getRepository
   * @covers ::getRepositoryUrl
   * @covers ::getNormalizedRepository
   */
  public function testGetters() : void {
    $sut = ProjectMetadata::createFromArray([
      'repository' => 'City-of-Helsinki/test',
      'azure_devops_link' => 'https://example.com/2',
    ]);
    $this->assertEquals('City-of-Helsinki/test', $sut->getRepository());
    $this->assertEquals('city-of-helsinki/test', $sut->getNormalizedRepository());
    $this->assertEquals('https://github.com/City-of-Helsinki/test', $sut->getRepositoryUrl());
    $this->assertEquals('https://example.com/2', $sut->getAzureDevopsLink());
  }

}

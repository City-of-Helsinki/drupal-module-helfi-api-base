<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\helfi_api_base\Environment\ProjectMetadata;
use Drupal\Tests\UnitTestCase;

/**
 * Tests Project metadata value object.
 *
 * @group helfi_api_base
 */
class ProjectMetadataTest extends UnitTestCase {

  /**
   * Tests constructor validation.
   */
  public function testRequiredValueException() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The repositoryUrl must be a valid URL.');
    new ProjectMetadata('');
  }

  /**
   * Test getter methods.
   */
  public function testGetters() : void {
    $sut = new ProjectMetadata('https://github.com/City-of-Helsinki/test');
    $this->assertEquals('City-of-Helsinki/test', $sut->getRepository());
    $this->assertEquals('city-of-helsinki/test', $sut->getNormalizedRepository());
    $this->assertEquals('https://github.com/City-of-Helsinki/test', $sut->getRepositoryUrl());
  }

}

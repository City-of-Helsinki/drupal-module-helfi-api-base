<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Environment\Address;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\helfi_api_base\Environment\ProjectMetadata;
use Drupal\helfi_api_base\Exception\EnvironmentException;
use Drupal\Tests\UnitTestCase;

/**
 * Tests Project value object.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\Project
 * @group helfi_api_base
 */
class ProjectTest extends UnitTestCase {

  /**
   * @covers ::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   */
  public function testConstructorException() : void {
    $caught = FALSE;
    try {
      // Make sure $environments parameter must be an Environment object.
      new Project(Project::ASUMINEN, new ProjectMetadata('', ''), ['invalid env']);
    }
    catch (\InvalidArgumentException) {
      $caught = TRUE;
    }
    $this->assertTrue($caught);
  }

  /**
   * @covers ::__construct
   * @covers ::getName
   * @covers ::getMetadata
   * @covers ::getEnvironments
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   */
  public function testGetters() : void {
    $sut = new Project(Project::ASUMINEN, new ProjectMetadata('', ''), [
      new Environment(
        new Address('www.hel.fi'),
        new Address('www.hel.fi'),
        [],
        Project::ASUMINEN,
        EnvironmentEnum::Local,
        NULL
      ),
    ]);
    $this->assertEquals(Project::ASUMINEN, $sut->getName());
    $this->assertInstanceOf(ProjectMetadata::class, $sut->getMetadata());
    $this->assertInstanceOf(Environment::class, $sut->getEnvironments()[0]);
  }

  /**
   * @covers ::getEnvironment
   * @covers ::hasEnvironment
   * @covers ::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentTrait::normalizeEnvironmentName
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   */
  public function testGetEnvironmentException() : void {
    $caught = FALSE;
    $sut = new Project(Project::ASUMINEN, new ProjectMetadata('', ''));
    try {
      $sut->getEnvironment('local');
    }
    catch (EnvironmentException $e) {
      $this->assertEquals('Environment "local" not found.', $e->getMessage());
      $caught = TRUE;
    }
    $this->assertTrue($caught);
  }

  /**
   * @covers ::__construct
   * @covers ::label
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   */
  public function testLabel() : void {
    $constants = new \ReflectionClass(Project::class);
    $found = 0;
    foreach ($constants->getConstants() as $value) {
      $found++;
      $sut = new Project($value, new ProjectMetadata('', ''));
      $this->assertInstanceOf(TranslatableMarkup::class, $sut->label());
    }
    $this->assertTrue($found > 0);
  }

}

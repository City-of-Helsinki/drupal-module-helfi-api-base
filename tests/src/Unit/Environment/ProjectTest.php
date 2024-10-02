<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Environment\Address;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\helfi_api_base\Environment\ProjectMetadata;
use Drupal\Tests\UnitTestCase;

/**
 * Tests Project value object.
 *
 * @group helfi_api_base
 */
class ProjectTest extends UnitTestCase {

  /**
   * Validate constructor arguments.
   */
  public function testConstructorException() : void {
    $this->expectException(\InvalidArgumentException::class);
    // Make sure $environments parameter must be an Environment object.
    new Project(Project::ASUMINEN, new ProjectMetadata(''), ['invalid env']);
  }

  /**
   * Test getters.
   */
  public function testGetters() : void {
    $sut = new Project(Project::ASUMINEN, new ProjectMetadata('https://github.com/city-of-helsinki/something'), [
      new Environment(
        new Address('www.hel.fi'),
        new Address('www.hel.fi'),
        [],
        EnvironmentEnum::Local,
      ),
    ]);
    $this->assertEquals(Project::ASUMINEN, $sut->getName());
    $this->assertInstanceOf(ProjectMetadata::class, $sut->getMetadata());
    $this->assertInstanceOf(Environment::class, $sut->getEnvironments()[EnvironmentEnum::Local->value]);
  }

  /**
   * Validate environment.
   */
  public function testGetEnvironmentException() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Environment "local" not found.');
    $sut = new Project(Project::ASUMINEN, new ProjectMetadata('https://github.com/city-of-helsinki/something'));
    $sut->getEnvironment('local');
  }

  /**
   * Test label.
   */
  public function testLabel() : void {
    $constants = new \ReflectionClass(Project::class);
    $found = 0;
    foreach ($constants->getConstants() as $value) {
      $found++;
      $sut = new Project($value, new ProjectMetadata('https://github.com/city-of-helsinki/something'));
      $this->assertInstanceOf(TranslatableMarkup::class, $sut->label());
    }
    $this->assertTrue($found > 0);
  }

}

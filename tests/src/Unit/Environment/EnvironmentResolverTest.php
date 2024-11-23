<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Tests\UnitTestCase;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;
use Drupal\helfi_api_base\Environment\Project;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests environment resolver value objects.
 *
 * @group helfi_api_base
 */
class EnvironmentResolverTest extends UnitTestCase {

  use ProphecyTrait;
  use EnvironmentResolverTrait;

  /**
   * Ensures all defined projects have a matching constant.
   */
  public function testProjectConstant() : void {
    $constants = new \ReflectionClass(Project::class);
    $resolver = new EnvironmentResolver($this->getConfigStub());

    foreach ($constants->getConstants() as $value) {
      $this->assertNotEmpty($resolver->getProject($value));
    }
    // Make sure all projects have constant.
    $this->assertEquals(count($resolver->getProjects()), count($constants->getConstants()));
  }

  /**
   * Tests getEnvironment() validation.
   *
   * @dataProvider resolveEnvironmentExceptionData
   */
  public function testGetEnvironmentException(
    string $project,
    string $language,
    string $environment,
    string $message,
  ) : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $this->getEnvironmentResolver()
      ->getEnvironment($project, $environment);
  }

  /**
   * Data provider.
   *
   * @return array
   *   The data.
   */
  public function resolveEnvironmentExceptionData() : array {
    return [
      ['nonexistent', '', '', 'Project "nonexistent" not found.'],
      ['asuminen', 'en', 'nonexistent', 'Environment "nonexistent" not found.'],
    ];
  }

  /**
   * Test environment mapping.
   *
   * @dataProvider environmentMapData
   */
  public function testEnvironmentMap(string $envName, string $expected) : void {
    $env = $this->getEnvironmentResolver()
      ->getEnvironment(Project::ASUMINEN, $envName);
    $this->assertEquals($expected, $env->getEnvironmentName());
  }

  /**
   * Data provider for testEnvironmentMap().
   *
   * @return array
   *   The data.
   */
  public function environmentMapData() : array {
    return [
      ['ci', 'test'],
      ['testing', 'test'],
      ['production', 'prod'],
      ['staging', 'stage'],
    ];
  }

  /**
   * Test ::getActiveProject() validation.
   *
   * @dataProvider activeProjectExceptionData
   */
  public function testGetActiveProjectException(mixed $value) : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/^No active project found./');

    // Construct config mock manually because ::getConfigStub() will never
    // return boolean.
    $config = $this->prophesize(ImmutableConfig::class);
    $config->get(Argument::any())->willReturn($value);
    $configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $configFactory->get(Argument::any())
      ->willReturn($config->reveal());
    $sut = new EnvironmentResolver($configFactory->reveal());
    $sut->getActiveProject();
  }

  /**
   * Data provider for active project exception test.
   *
   * @return array
   *   The data.
   */
  public function activeProjectExceptionData() : array {
    return [
      [NULL],
      [FALSE],
      [''],
    ];
  }

  /**
   * Test getActiveProject().
   */
  public function testGetActiveProject() : void {
    $sut = $this->getEnvironmentResolver(Project::ASUMINEN, 'dev');
    $this->assertInstanceOf(Project::class, $sut->getActiveProject());
    $this->assertCount(4, $sut->getActiveProject()->getEnvironments());
  }

  /**
   * Tests getActiveEnvironment() validation.
   */
  public function testGetActiveEnvironmentException() : void {
    putenv('APP_ENV=');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/^No active environment found./');
    $this->getEnvironmentResolver(Project::ASUMINEN)->getActiveEnvironment();
  }

  /**
   * Make sure getActiveEnvironment() fallbacks to env variable.
   */
  public function testGetActiveEnvironmentFallback() : void {
    // Make sure environment resolver fallbacks to APP_ENV env variable when
    // active environment configuration is not set.
    putenv('APP_ENV=random');
    $this->assertEquals('random', $this->getEnvironmentResolver(Project::ASUMINEN)->getActiveEnvironmentName());
  }

  /**
   * Tests getActiveEnvironment().
   */
  public function testGetActiveEnvironment() : void {
    $sut = $this->getEnvironmentResolver(Project::ASUMINEN, EnvironmentEnum::Test);
    $this->assertInstanceOf(Environment::class, $sut->getActiveEnvironment());
  }

}

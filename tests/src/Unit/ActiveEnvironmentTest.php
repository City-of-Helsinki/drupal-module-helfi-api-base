<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\helfi_api_base\Environment\ActiveEnvironment;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\Tests\UnitTestCase;

/**
 * Tests active environment.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\ActiveEnvironment
 * @group helfi_api_base
 */
class ActiveEnvironmentTest extends UnitTestCase {

  /**
   * Constructs an "active environment" resolver instance.
   *
   * @param string|null $projectName
   *   The project name.
   * @param string|null $envName
   *   The environment name.
   *
   * @return \Drupal\helfi_api_base\Environment\ActiveEnvironment
   *   The active environment resolver.
   */
  private function getActiveEnvironment(string $projectName = NULL, string $envName = NULL) : ActiveEnvironment {
    $config = [];

    if ($projectName) {
      $config[ActiveEnvironment::PROJECT_NAME_KEY] = $projectName;
    }
    if ($envName) {
      $config[ActiveEnvironment::ENVIRONMENT_NAME_KEY] = $envName;
    }
    $configStub = $this->getConfigFactoryStub([
      'helfi_api_base.environment_resolver.settings' => $config,
    ]);
    return new ActiveEnvironment($configStub, new EnvironmentResolver(''));
  }

  /**
   * @covers ::getActiveProject
   * @covers ::getActiveProjectName
   * @covers ::__construct
   * @covers ::configurationMissingExceptionMessage
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   */
  public function testGetActiveProjectException() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/^No active project found./');
    $this->getActiveEnvironment()->getActiveProject();
  }

  /**
   * @covers ::getActiveProject
   * @covers ::getActiveProjectName
   * @covers ::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::getProject
   */
  public function testGetActiveProject() : void {
    $sut = $this->getActiveEnvironment(Project::ASUMINEN, 'dev');
    $this->assertInstanceOf(Project::class, $sut->getActiveProject());
  }

  /**
   * @covers ::__construct
   * @covers ::getActiveEnvironment
   * @covers ::getActiveProject
   * @covers ::getActiveProjectName
   * @covers ::getActiveEnvironmentName
   * @covers ::configurationMissingExceptionMessage
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::getProject
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   */
  public function testGetActiveEnvironmentException() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/^No active environment found./');
    $this->getActiveEnvironment(Project::ASUMINEN)->getActiveEnvironment();
  }

  /**
   * @covers ::__construct
   * @covers ::getActiveEnvironment
   * @covers ::getActiveEnvironmentName
   * @covers ::getActiveProject
   * @covers ::getActiveProjectName
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::getProject
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::mapEnvironmentName
   */
  public function testGetActiveEnvironment() : void {
    $sut = $this->getActiveEnvironment(Project::ASUMINEN, 'dev');
    $this->assertInstanceOf(Environment::class, $sut->getActiveEnvironment());
  }

}

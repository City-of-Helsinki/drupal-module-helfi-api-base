<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests environment resolver value objects.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\EnvironmentResolver
 * @group helfi_api_base
 */
class EnvironmentResolverTest extends UnitTestCase {

  use ProphecyTrait;
  use EnvironmentResolverTrait;

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getProjects
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::hasEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentTrait::normalizeEnvironmentName
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
   */
  public function testProjectConstant() : void {
    $constants = new \ReflectionClass(Project::class);
    $resolver = new EnvironmentResolver('', $this->getConfigStub());

    foreach ($constants->getConstants() as $value) {
      $this->assertNotEmpty($resolver->getProject($value));
    }
    // Make sure all projects have constant.
    $this->assertEquals(count($resolver->getProjects()), count($constants->getConstants()));
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @dataProvider populateEnvironmentsExceptionsData
   */
  public function testPopulateEnvironmentsExceptions(string $data, string $message) : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    new EnvironmentResolver($data, $this->getConfigStub());
  }

  /**
   * Data provider.
   *
   * @return array
   *   The data.
   */
  public function populateEnvironmentsExceptionsData() : array {
    return [
      ['nonexistent.json', 'Environment file not found or is not a valid JSON.'],
      [__FILE__, 'Failed to parse projects.'],
      [
        json_encode([
          'asuminen' => [
            'meta' => [
              'repository' => '123',
              'azure_devops_link' => 'https://example.com',
            ],
            'environments' => [
              'local' => [],
            ],
          ],
        ]),
        'Project missing "address", "internal_address" or "paths" setting.',
      ],
      [
        json_encode([
          'asuminen' => [],
        ]),
        'Project missing meta or environments.',
      ],
    ];
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getProjectForRepository
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getUrl
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::hasEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::getMetadata
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentTrait::normalizeEnvironmentName
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::getNormalizedRepository
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
   * @dataProvider resolveEnvironmentExceptionData
   */
  public function testGetEnvironmentException(
    string $project,
    string $language,
    string $environment,
    string $message
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
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::getEnvironmentName
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::hasEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentTrait::normalizeEnvironmentName
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
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
      ['testing', 'test'],
      ['production', 'prod'],
      ['staging', 'stage'],
    ];
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getActiveProject
   * @covers ::getConfig
   * @covers ::configurationMissingExceptionMessage
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
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
    $sut = new EnvironmentResolver('', $configFactory->reveal());
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
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getActiveProject
   * @covers ::getConfig
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironments
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::getProject
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
   */
  public function testGetActiveProject() : void {
    $sut = $this->getEnvironmentResolver(Project::ASUMINEN, 'dev');
    $this->assertInstanceOf(Project::class, $sut->getActiveProject());
    $this->assertCount(4, $sut->getActiveProject()->getEnvironments());
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::configurationMissingExceptionMessage
   * @covers ::getActiveEnvironment
   * @covers ::getActiveEnvironmentName
   * @covers ::getConfig
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::getProject
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
   */
  public function testGetActiveEnvironmentException() : void {
    putenv('APP_ENV=');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/^No active environment found./');
    $this->getEnvironmentResolver(Project::ASUMINEN)->getActiveEnvironment();
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getActiveEnvironmentName
   * @covers ::getConfig
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentTrait::normalizeEnvironmentName
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
   */
  public function testGetActiveEnvironmentFallback() : void {
    // Make sure environment resolver fallbacks to APP_ENV env variable when
    // active environment configuration is not set.
    putenv('APP_ENV=random');
    $this->assertEquals('random', $this->getEnvironmentResolver(Project::ASUMINEN)->getActiveEnvironmentName());
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getConfig
   * @covers ::getActiveEnvironment
   * @covers ::getActiveEnvironmentName
   * @covers ::getActiveProject
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::getProject
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::hasEnvironment
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentTrait::normalizeEnvironmentName
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\ProjectMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::createFromArray
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
   */
  public function testGetActiveEnvironment() : void {
    $sut = $this->getEnvironmentResolver(Project::ASUMINEN, 'test');
    $this->assertInstanceOf(Environment::class, $sut->getActiveEnvironment());
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\Tests\UnitTestCase;

/**
 * Tests environment resolver value objects.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\EnvironmentResolver
 * @group helfi_api_base
 */
class EnvironmentResolverTest extends UnitTestCase {

  /**
   * Constructs a new config factory instance.
   *
   * @param string|null $projectName
   *   The project name.
   * @param string|null $envName
   *   The environment name.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory stub.
   */
  private function getConfigStub(string $projectName = NULL, string $envName = NULL) :  ConfigFactoryInterface {
    $config = [];

    if ($projectName) {
      $config[EnvironmentResolver::PROJECT_NAME_KEY] = $projectName;
    }
    if ($envName) {
      $config[EnvironmentResolver::ENVIRONMENT_NAME_KEY] = $envName;
    }
    return $this->getConfigFactoryStub([
      'helfi_api_base.environment_resolver.settings' => $config,
    ]);
  }

  /**
   * Gets the environment resolver.
   *
   * @return \Drupal\helfi_api_base\Environment\EnvironmentResolver
   *   The sut.
   */
  private function getEnvironmentResolver(string $projectName = NULL, string $envName = NULL) : EnvironmentResolver {
    $configStub = $this->getConfigStub($projectName, $envName);
    return new EnvironmentResolver(__DIR__ . '/../../fixtures/environments.json', $configStub);
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getProjects
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::mapEnvironmentName
   */
  public function testFallbackEnvironmentFile() : void {
    $resolver = new EnvironmentResolver('', $this->getConfigStub());
    $this->assertTrue(count($resolver->getProjects()) > 5);
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getProjects
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::mapEnvironmentName
   */
  public function testProjectConstant() : void {
    $constants = new \ReflectionClass(Project::class);
    $resolver = new EnvironmentResolver('', $this->getConfigStub());

    foreach ($constants->getConstants() as $value) {
      $this->assertNotEmpty($resolver->getProject($value));
    }

    // Make sure we have multiple projects.
    $this->assertTrue(count($resolver->getProjects()) > 5);
    // Make sure all projects have constant.
    $this->assertEquals(count($resolver->getProjects()), count($constants->getConstants()));
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @dataProvider populateEnvironmentsExceptionsData
   */
  public function testPopulateEnvironmentsExceptions(string $file, string $message) : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    new EnvironmentResolver($file, $this->getConfigStub());
  }

  /**
   * Data provider.
   *
   * @return array
   *   The data.
   */
  public function populateEnvironmentsExceptionsData() : array {
    return [
      ['nonexistent.json', 'Environment file not found.'],
      [__FILE__, 'Failed to parse projects.'],
      [
        __DIR__ . '/../../fixtures/invalidenvironment.json',
        'Project missing domain or paths setting.',
      ],
    ];
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
   * @covers \Drupal\helfi_api_base\Environment\Environment::getProtocol
   * @covers \Drupal\helfi_api_base\Environment\Environment::getBaseUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::getUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::doGetUrl
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::mapEnvironmentName
   * @dataProvider resolvePathExceptionData
   */
  public function testResolveUrlException(
    string $project,
    string $language,
    string $environment,
    string $message
  ) : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $this->getEnvironmentResolver()
      ->getEnvironment($project, $environment)
      ->getUrl($language);
  }

  /**
   * Data provider.
   *
   * @return \string[][]
   *   The data.
   */
  public function resolvePathExceptionData() : array {
    return [
      ['nonexistent', '', '', 'Project "nonexistent" not found.'],
      ['asuminen', 'sk', 'dev', 'Path not found for "sk" language.'],
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
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
   * @covers \Drupal\helfi_api_base\Environment\Environment::getProtocol
   * @covers \Drupal\helfi_api_base\Environment\Environment::getBaseUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::getUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::doGetUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::getEnvironmentName
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::mapEnvironmentName
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
   * @return \string[][]
   *   The data.
   */
  public function environmentMapData() : array {
    return [
      ['development', 'dev'],
      ['devel', 'dev'],
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
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::doGetUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
   * @covers \Drupal\helfi_api_base\Environment\Environment::getProtocol
   * @covers \Drupal\helfi_api_base\Environment\Environment::getBaseUrl
   * @covers \Drupal\helfi_api_base\Environment\Environment::getInternalAddress
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::getEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\Project::mapEnvironmentName
   * @dataProvider validUrlData
   */
  public function testValidUrl(
    string $project,
    string $language,
    string $environment,
    string $expected,
    string $expectedInternal
  ) : void {
    $url = $this->getEnvironmentResolver()
      ->getEnvironment($project, $environment)
      ->getUrl($language);
    $this->assertEquals($expected, $url);

    $internalUrl = $this->getEnvironmentResolver()
      ->getEnvironment($project, $environment)
      ->getInternalAddress($language);
    $this->assertEquals($expectedInternal, $internalUrl);
  }

  /**
   * Data provider.
   *
   * @return \string[][]
   *   The data.
   */
  public function validUrlData() : array {
    return [
      [
        'asuminen',
        'fi',
        'dev',
        'https://helfi-asuminen-dev.docker.so/fi/dev-asuminen',
        'https://helfi-asuminen-dev.docker.so/fi/dev-asuminen',
      ],
      [
        'asuminen',
        'en',
        'dev',
        'https://helfi-asuminen-dev.docker.so/en/dev-housing',
        'https://helfi-asuminen-dev.docker.so/en/dev-housing',
      ],
      [
        'asuminen',
        'sv',
        'dev',
        'https://helfi-asuminen-dev.docker.so/sv/dev-boende',
        'https://helfi-asuminen-dev.docker.so/sv/dev-boende',
      ],
      [
        'asuminen',
        'fi',
        'prod',
        'https://www.hel.fi/fi/asuminen',
        'https://www.hel.fi/fi/asuminen',
      ],
      [
        'asuminen',
        'en',
        'prod',
        'https://www.hel.fi/en/housing',
        'https://www.hel.fi/en/housing',
      ],
      [
        'asuminen',
        'sv',
        'prod',
        'https://www.hel.fi/sv/boende',
        'https://www.hel.fi/sv/boende',
      ],
      [
        'asuminen',
        'sv',
        'local',
        'https://helfi-asuminen.docker.so/sv/boende',
        'http://helfi-asuminen.docker.so:8080/sv/boende',
      ],
    ];
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getActiveProject
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
    $this->getEnvironmentResolver()->getActiveProject();
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getActiveProject
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::populateEnvironments
   * @covers \Drupal\helfi_api_base\Environment\Project::__construct
   * @covers \Drupal\helfi_api_base\Environment\Project::addEnvironment
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentResolver::getProject
   */
  public function testGetActiveProject() : void {
    $sut = $this->getEnvironmentResolver(Project::ASUMINEN, 'dev');
    $this->assertInstanceOf(Project::class, $sut->getActiveProject());
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::configurationMissingExceptionMessage
   * @covers ::getActiveEnvironment
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
    $this->getEnvironmentResolver(Project::ASUMINEN)->getActiveEnvironment();
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getEnvironment
   * @covers ::getProject
   * @covers ::getActiveEnvironment
   * @covers ::getActiveProject
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
    $sut = $this->getEnvironmentResolver(Project::ASUMINEN, 'dev');
    $this->assertInstanceOf(Environment::class, $sut->getActiveEnvironment());
  }

}

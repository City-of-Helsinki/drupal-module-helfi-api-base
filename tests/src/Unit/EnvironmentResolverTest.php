<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_tpr\Unit;

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
   * Gets the environment resolver.
   *
   * @return \Drupal\helfi_api_base\Environment\EnvironmentResolver
   *   The sut.
   */
  private function getEnvironmentResolver() : EnvironmentResolver {
    return new EnvironmentResolver(__DIR__ . '/../../fixtures/environments.json');
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getProjectEnvironment
   * @covers ::getPath
   * @covers ::getDomain
   * @covers ::getUrl
   * @covers ::getProject
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
   */
  public function testFallbackEnvironmentFile() : void {
    new EnvironmentResolver('');
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getProjectEnvironment
   * @covers ::getPath
   * @covers ::getDomain
   * @covers ::getUrl
   * @covers ::getProject
   * @covers ::getProjects
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
   */
  public function testProjectConstant() : void {
    $constants = new \ReflectionClass(Project::class);
    $resolver = new EnvironmentResolver('');

    foreach ($constants->getConstants() as $value) {
      $this->assertNotEmpty($resolver->getProject($value));
    }

    // Make sure all projects have constant.
    $this->assertEquals(count($resolver->getProjects()), count($constants->getConstants()));
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @dataProvider populateEnvironmentsExceptionsData
   */
  public function testPopulateEnvironmentsExceptions(string $file, string $message) : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    new EnvironmentResolver($file);
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
   * @covers ::getProjectEnvironment
   * @covers ::getPath
   * @covers ::getDomain
   * @covers ::getUrl
   * @covers ::getProject
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
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
      ->getUrl($project, $language, $environment);
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
      ['asuminen', 'en', 'local', 'Environment "local" not found.'],
    ];
  }

  /**
   * @covers ::populateEnvironments
   * @covers ::__construct
   * @covers ::getProjectEnvironment
   * @covers ::getPath
   * @covers ::getDomain
   * @covers ::getUrl
   * @covers ::getProject
   * @covers \Drupal\helfi_api_base\Environment\Environment::__construct
   * @covers \Drupal\helfi_api_base\Environment\Environment::getPath
   * @covers \Drupal\helfi_api_base\Environment\Environment::getDomain
   * @dataProvider validUrlData
   */
  public function testValidUrl(
    string $project,
    string $language,
    string $environment,
    string $expected
  ) : void {
    $url = $this->getEnvironmentResolver()
      ->getUrl($project, $language, $environment);
    $this->assertEquals($expected, $url);
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
      ],
      [
        'asuminen',
        'en',
        'dev',
        'https://helfi-asuminen-dev.docker.so/en/dev-housing',
      ],
      [
        'asuminen',
        'sv',
        'dev',
        'https://helfi-asuminen-dev.docker.so/sv/dev-boende',
      ],
      [
        'asuminen',
        'fi',
        'prod',
        'https://helfi-asuminen.docker.so/fi/asuminen',
      ],
      [
        'asuminen',
        'en',
        'prod',
        'https://helfi-asuminen.docker.so/en/housing',
      ],
      [
        'asuminen',
        'sv',
        'prod',
        'https://helfi-asuminen.docker.so/sv/boende',
      ],
    ];
  }

}

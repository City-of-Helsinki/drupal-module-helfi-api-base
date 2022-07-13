<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

/**
 * Environment resolver.
 */
final class EnvironmentResolver {

  /**
   * The cached environments.
   *
   * @var array
   */
  private array $environments;

  /**
   * Constructs a new instance.
   *
   * @param string $path
   *   The path to environments.json file.
   */
  public function __construct(string $path) {
    $this->populateEnvironments($path);
  }

  /**
   * Populates the environments for given json config file.
   *
   * @param string $path
   *   The path to config.json file.
   */
  private function populateEnvironments(string $path) : void {
    // Fallback to default environments.json file.
    if ($path === '') {
      $path = __DIR__ . '/../../fixtures/environments.json';
    }
    if (!file_exists($path)) {
      throw new \InvalidArgumentException('Environment file not found.');
    }

    $projects = json_decode(file_get_contents($path), TRUE);

    if (empty($projects)) {
      throw new \InvalidArgumentException('Failed to parse projects.');
    }

    foreach ($projects as $name => $project) {
      foreach ($project as $environment => $settings) {
        if (!isset($settings['domain'], $settings['path'])) {
          throw new \InvalidArgumentException('Project missing domain or paths setting.');
        }
        $this->environments[$name][$environment] = new Environment(
          $settings['domain'],
          $settings['path'],
          $settings['protocol'] ?? 'https',
          $name
        );

        // Create 'internal' environment that points to currently active
        // instance. It should contain identical settings with local environment
        // except for domain.
        if ($environment === 'local') {
          $this->environments[$name]['internal'] = new Environment(
            '127.0.0.1:8080',
            $settings['path'],
            'http',
            $name
          );
        }
      }
    }
  }

  /**
   * Gets all projects.
   *
   * @return array
   *   The projects.
   */
  public function getProjects() : array {
    return $this->environments;
  }

  /**
   * Gets the project data.
   *
   * @param string $project
   *   The project name.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment[]
   *   The project environments.
   */
  public function getProject(string $project) : array {
    if (!isset($this->environments[$project])) {
      throw new \InvalidArgumentException(sprintf('Project "%s" not found.', $project));
    }
    return $this->environments[$project];
  }

  /**
   * Gets the environment for given project.
   *
   * @param string $project
   *   The project name.
   * @param string $environment
   *   The environment name.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment
   *   The environment.
   */
  public function getEnvironment(string $project, string $environment) : Environment {
    $project = $this->getProject($project);

    if (!isset($project[$environment])) {
      throw new \InvalidArgumentException(sprintf('Environment "%s" not found.', $environment));
    }
    return $project[$environment];
  }

  /**
   * Find environment by url.
   *
   * @param string $url
   *   Url to search for.
   *
   * @return Environment
   */
  public function getEnvironmentByUrl(string $url): Environment {
    foreach($this->getProjects() as $environments) {
      foreach($environments as $environment) {
        if ($environment->getDomain() === $url) {
           return $environment;
        }
      }
    }
    throw new \InvalidArgumentException(sprintf('Environment not found by url %s', $url));
  }

  /**
   * Temporary mapping function to match APP_ENV with environment resolver.
   *
   * @param $env
   *   APP_ENV or environment name
   *
   * @return string
   *   Current environment name translated enviroment resolver enviroment name.
   */
  public static function getCurrentEnvironmentName($env = NULL) {
    // Dev,test,stage,prod are the environment names in environment resolver.
    // APP_ENV values on environments doesn't match the environment resolver.
    // Thus a mapping is required.
    $env = $env ?? getenv('APP_ENV');

    $environments = [
      'local' => 'local',
      'dev' => 'dev',
      'devel' => 'dev',
      'development' => 'dev',
      'test' => 'test',
      'testing' => 'test',
      'stage' => 'stage',
      'staging' => 'stage',
      'prod' => 'prod',
      'production' => 'prod'
    ];

    if (array_key_exists($env, $environments)) {
      return $environments[$env];
    }

    throw new \InvalidArgumentException(sprintf('%s is not a proper environment name', $env));
  }

}

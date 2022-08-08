<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

/**
 * Environment resolver.
 */
final class EnvironmentResolver {

  /**
   * The cached projects.
   *
   * @var \Drupal\helfi_api_base\Environment\Project[]
   */
  private array $projects;

  /**
   * Constructs a new instance.
   *
   * @param string $path
   *   The path to environments.json file.
   */
  public function __construct(string $path) {
    $this->populateProjects($path);
  }

  /**
   * Populates the environments for given json config file.
   *
   * @param string $path
   *   The path to config.json file.
   */
  private function populateProjects(string $path) : void {
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

    foreach ($projects as $name => $item) {
      $project = new Project();

      foreach ($item as $environment => $settings) {
        if (!isset($settings['domain'], $settings['path'])) {
          throw new \InvalidArgumentException('Project missing domain or paths setting.');
        }
        $project->addEnvironment($environment, new Environment(
          $settings['domain'],
          $settings['path'],
          $settings['protocol'] ?? 'https',
          $name,
          $environment
        ));
      }
      $this->projects[$name] = $project;
    }
  }

  /**
   * Gets all projects.
   *
   * @return array
   *   The projects.
   */
  public function getProjects() : array {
    return $this->projects;
  }

  /**
   * Gets the project data.
   *
   * @param string $project
   *   The project name.
   *
   * @return \Drupal\helfi_api_base\Environment\Project
   *   The project.
   */
  public function getProject(string $project) : Project {
    if (!isset($this->projects[$project])) {
      throw new \InvalidArgumentException(sprintf('Project "%s" not found.', $project));
    }
    return $this->projects[$project];
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
    return $this->getProject($project)
      ->getEnvironment($environment);
  }

  /**
   * Find environment by url.
   *
   * @param string $url
   *   Url to search for.
   *
   * @return Environment
   *   Environment object.
   */
  public function getEnvironmentByUrl(string $url) : Environment {
    foreach ($this->getProjects() as $environments) {
      foreach ($environments as $environment) {
        if ($environment->getDomain() === $url) {
          return $environment;
        }
      }
    }
    throw new \InvalidArgumentException(sprintf('Environment not found by url %s', $url));
  }

}

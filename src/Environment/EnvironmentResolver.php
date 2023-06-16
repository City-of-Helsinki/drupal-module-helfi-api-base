<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Environment resolver.
 */
final class EnvironmentResolver implements EnvironmentResolverInterface {

  use EnvironmentTrait;

  public const PROJECT_NAME_KEY = 'project_name';
  public const ENVIRONMENT_NAME_KEY = 'environment_name';

  /**
   * The cached projects.
   *
   * @var \Drupal\helfi_api_base\Environment\Project[]
   */
  private array $projects;

  /**
   * The environment name.
   *
   * @var string $activeEnvironmentName
   */
  protected string $activeEnvironmentName;

  /**
   * The project.
   *
   * @var \Drupal\helfi_api_base\Environment\Project
   */
  protected Project $activeProject;

  /**
   * Constructs a new instance.
   *
   * @param string $pathOrJson
   *   The path to environments.json file.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(
    string $pathOrJson,
    private readonly ConfigFactoryInterface $configFactory
  ) {
    $this->populateEnvironments($pathOrJson);
  }

  /**
   * Populates the environments for given json config file.
   *
   * @param string $pathOrJson
   *   The path to config.json file.
   */
  private function populateEnvironments(string $pathOrJson) : void {
    // Fallback to default environments.json file.
    if ($pathOrJson === '') {
      $pathOrJson = __DIR__ . '/../../fixtures/environments.json';
    }

    if (!is_file($pathOrJson)) {
      try {
        $projects = json_decode($pathOrJson, TRUE, flags: JSON_THROW_ON_ERROR);
      }
      catch (\JsonException) {
        throw new \InvalidArgumentException('Environment file not found or is not a valid JSON.');
      }
    }
    else {
      $projects = json_decode(file_get_contents($pathOrJson), TRUE);
    }

    if (empty($projects)) {
      throw new \InvalidArgumentException('Failed to parse projects.');
    }

    foreach ($projects as $id => $item) {
      if (!isset($item['meta'], $item['environments'])) {
        throw new \InvalidArgumentException('Project missing meta or environments.');
      }
      ['meta' => $meta, 'environments' => $environments] = $item;

      $project = new Project($id, ProjectMetadata::createFromArray($meta));

      foreach ($environments as $environment => $settings) {
        if (!isset($settings['domain'], $settings['path'])) {
          throw new \InvalidArgumentException('Project missing domain or paths setting.');
        }
        $project->addEnvironment($environment, new Environment(
          $settings['domain'],
          $settings['path'],
          $settings['protocol'] ?? 'https',
          $id,
          EnvironmentEnum::tryFrom($environment),
          EnvironmentMetadata::createFromArray($settings['meta'] ?? [])
        ));
      }
      $this->projects[$id] = $project;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getProjects() : array {
    return $this->projects;
  }

  /**
   * Gets the configuration value for given key.
   *
   * @param string $key
   *   The key.
   *
   * @return string|null
   *   The configuration value or null.
   */
  private function getConfig(string $key) : ?string {
    return $this->configFactory
      ->get('helfi_api_base.environment_resolver.settings')
      ->get($key) ?: NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveProject() : Project {
    if (!empty($this->activeProject)) {
      return $this->activeProject;
    }
    if (!$name = $this->getConfig(self::PROJECT_NAME_KEY)) {
      throw new \InvalidArgumentException(
        $this->configurationMissingExceptionMessage('No active project found', self::PROJECT_NAME_KEY)
      );
    }
    return $this->activeProject = $this
      ->getProject($name);
  }

  /**
   * Gets the active environment configuration.
   *
   * @return string
   *   The active environment name.
   */
  public function getActiveEnvironmentName() : string {
    if (!empty($this->activeEnvironmentName)) {
      return $this->activeEnvironmentName;
    }
    if (!$env = $this->getConfig(self::ENVIRONMENT_NAME_KEY)) {
      // Fallback to APP_ENV env variable.
      $env = getenv('APP_ENV');
    }
    if (!$env) {
      throw new \InvalidArgumentException(
        $this->configurationMissingExceptionMessage('No active environment found', self::ENVIRONMENT_NAME_KEY)
      );
    }
    return $this->activeEnvironmentName = $this->normalizeEnvironmentName($env);
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveEnvironment() : Environment {
    $env = $this->getActiveEnvironmentName();

    return $this->getActiveProject()
      ->getEnvironment($env);
  }

  /**
   * Generate a generic message for missing configuration.
   *
   * @param string $message
   *   The message.
   * @param string $configName
   *   The name of the missing configuration.
   *
   * @return string
   *   The exception message.
   */
  private function configurationMissingExceptionMessage(string $message, string $configName) : string {
    return sprintf('%s. Please set "helfi_api_base.environment_resolver.%s" configuration.', $message, $configName);
  }

  /**
   * Gets the project for given repository.
   *
   * @param string $repository
   *   The repository name.
   *
   * @return \Drupal\helfi_api_base\Environment\Project
   *   The project.
   */
  private function getProjectForRepository(string $repository) : Project {
    $projects = array_filter(
      $this->projects,
      fn (Project $project) => $project->getMetadata()->getNormalizedRepository() === strtolower($repository)
    );

    if ($project = reset($projects)) {
      return $project;
    }
    throw new \InvalidArgumentException(
      sprintf('Project "%s" not found.', $repository)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getProject(string $project) : Project {
    if (!isset($this->projects[$project])) {
      return $this->getProjectForRepository($project);
    }
    return $this->projects[$project];
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironment(string $project, string $environment) : Environment {
    return $this->getProject($project)
      ->getEnvironment($environment);
  }

}

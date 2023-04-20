<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

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
   * The configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $config;

  /**
   * Constructs a new instance.
   *
   * @param string $pathOrJson
   *   The path to environments.json file.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(string $pathOrJson, ConfigFactoryInterface $configFactory) {
    $this->populateEnvironments($pathOrJson);
    $this->config = $configFactory->get('helfi_api_base.environment_resolver.settings');
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

      $project = new Project($id, Metadata::createFromArray($meta));

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
   * {@inheritdoc}
   */
  public function getActiveProject() : Project {
    if (!$name = $this->config->get(self::PROJECT_NAME_KEY)) {
      throw new \InvalidArgumentException(
        $this->configurationMissingExceptionMessage('No active project found', self::PROJECT_NAME_KEY)
      );
    }
    return $this
      ->getProject($name);
  }

  /**
   * Gets the active environment configuration.
   *
   * @return string
   *   The active environment name.
   */
  public function getActiveEnvironmentName() : string {
    if (!$env = $this->config->get(self::ENVIRONMENT_NAME_KEY)) {
      // Fallback to APP_ENV env variable.
      $env = getenv('APP_ENV');
    }
    if (!$env) {
      throw new \InvalidArgumentException(
        $this->configurationMissingExceptionMessage('No active environment found', self::ENVIRONMENT_NAME_KEY)
      );
    }
    return $this->normalizeEnvironmentName($env);
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
   * {@inheritdoc}
   */
  public function getProject(string $project) : Project {
    if (!isset($this->projects[$project])) {
      throw new \InvalidArgumentException(sprintf('Project "%s" not found.', $project));
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

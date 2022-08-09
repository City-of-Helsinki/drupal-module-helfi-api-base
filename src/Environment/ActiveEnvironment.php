<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Resolvers currently active environment.
 */
final class ActiveEnvironment {

  public const PROJECT_NAME_KEY = 'project_name';
  public const ENVIRONMENT_NAME_KEY = 'environment_name';

  /**
   * The configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $config;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentResolver $environmentResolver
   *   The environment resolver.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    private EnvironmentResolver $environmentResolver
  ) {
    $this->config = $configFactory->get('helfi_api_base.environment_resolver.settings');
  }

  /**
   * Gets the currently active project.
   *
   * @return \Drupal\helfi_api_base\Environment\Project
   *   The currently active project.
   */
  public function getActiveProject() : Project {
    return $this->environmentResolver
      ->getProject($this->getActiveProjectName());
  }

  /**
   * Gets the currently active environment.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment
   *   The currently active environment.
   */
  public function getActiveEnvironment() : Environment {
    return $this->getActiveProject()
      ->getEnvironment($this->getActiveEnvironmentName());
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
   * Gets the environment name.
   *
   * @return string
   *   The environment name.
   */
  public function getActiveEnvironmentName() : string {
    if (!$env = $this->config->get(self::ENVIRONMENT_NAME_KEY)) {
      throw new \InvalidArgumentException(
        $this->configurationMissingExceptionMessage('No active environment found', self::ENVIRONMENT_NAME_KEY)
      );
    }
    return $env;
  }

  /**
   * Gets the currently active project name.
   *
   * @return string
   *   The project name.
   */
  public function getActiveProjectName() : string {
    if (!$name = $this->config->get(self::PROJECT_NAME_KEY)) {
      throw new \InvalidArgumentException(
        $this->configurationMissingExceptionMessage('No active project found', self::PROJECT_NAME_KEY)
      );
    }
    return $name;
  }

}

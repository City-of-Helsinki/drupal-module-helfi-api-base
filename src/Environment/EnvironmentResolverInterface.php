<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

/**
 * An interface representing environment resolver.
 */
interface EnvironmentResolverInterface {

  /**
   * Gets all projects.
   *
   * @return \Drupal\helfi_api_base\Environment\Project[]
   *   The projects.
   */
  public function getProjects() : array;

  /**
   * Gets the currently active project.
   *
   * @return \Drupal\helfi_api_base\Environment\Project
   *   The currently active project.
   */
  public function getActiveProject() : Project;

  /**
   * Gets the currently active environment.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment
   *   The currently active environment.
   */
  public function getActiveEnvironment() : Environment;

  /**
   * Gets the active environment configuration.
   *
   * @return string
   *   The active environment name.
   */
  public function getActiveEnvironmentName() : string;

  /**
   * Gets the project data.
   *
   * @param string $project
   *   The project name.
   *
   * @return \Drupal\helfi_api_base\Environment\Project
   *   The project.
   */
  public function getProject(string $project) : Project;

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
  public function getEnvironment(string $project, string $environment) : Environment;

  /**
   * Populates the active project settings.
   *
   * @param string $project
   *   The project name.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentEnum $environment
   *   The environment.
   */
  public function populateActiveProjectSettings(string $project, EnvironmentEnum $environment) : void;

}

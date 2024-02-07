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
   *
   * @throws \Drupal\helfi_api_base\Exception\EnvironmentException
   */
  public function getActiveProject() : Project;

  /**
   * Gets the currently active environment.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment
   *   The currently active environment.
   *
   * @throws \Drupal\helfi_api_base\Exception\EnvironmentException
   */
  public function getActiveEnvironment() : Environment;

  /**
   * Gets the active environment configuration.
   *
   * @return string
   *   The active environment name.
   *
   * @throws \Drupal\helfi_api_base\Exception\EnvironmentException
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
   *
   * @throws \Drupal\helfi_api_base\Exception\EnvironmentException
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
   *
   * @throws \Drupal\helfi_api_base\Exception\EnvironmentException
   */
  public function getEnvironment(string $project, string $environment) : Environment;

}

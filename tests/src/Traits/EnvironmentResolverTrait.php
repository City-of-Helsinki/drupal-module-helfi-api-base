<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;

/**
 * A helper trait for environment resolver tests.
 */
trait EnvironmentResolverTrait {

  /**
   * Sets the active project.
   *
   * @param string $project
   *   The project.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentEnum $environment
   *   The environment.
   */
  public function setActiveProject(string $project, EnvironmentEnum $environment) : void {
    $this->container->get('config.factory')
      ->getEditable('helfi_api_base.environment_resolver.settings')
      ->set(EnvironmentResolver::PROJECT_NAME_KEY, $project)
      ->set(EnvironmentResolver::ENVIRONMENT_NAME_KEY, $environment->value)
      ->save();
  }

}

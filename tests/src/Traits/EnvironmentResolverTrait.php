<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\EnvironmentResolverInterface;

/**
 * A helper trait for environment resolver tests.
 */
trait EnvironmentResolverTrait {

  /**
   * The environment resolver.
   *
   * @var null|\Drupal\helfi_api_base\Environment\EnvironmentResolverInterface
   */
  protected ?EnvironmentResolverInterface $environmentResolver;

  /**
   * Gets the environment resolver service.
   *
   * @return \Drupal\helfi_api_base\Environment\EnvironmentResolverInterface
   *   The environment resolver service.
   */
  public function environmentResolver() : EnvironmentResolverInterface {
    if (!$this->environmentResolver) {
      $this->environmentResolver = $this->container->get('helfi_api_base.environment_resolver');
    }
    return $this->environmentResolver;
  }

  /**
   * Sets the active project.
   *
   * @param string $project
   *   The project.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentEnum $environment
   *   The environment.
   */
  public function setActiveProject(string $project, EnvironmentEnum $environment) : void {
    $this->environmentResolver()->setActiveProject($project, $environment);
  }

}

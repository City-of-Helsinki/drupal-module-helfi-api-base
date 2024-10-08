<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;

/**
 * A helper trait for environment resolver tests.
 */
trait EnvironmentResolverTrait {

  /**
   * The container.
   *
   * @var \Drupal\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

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

  /**
   * Constructs a new config factory instance.
   *
   * @param mixed $projectName
   *   The project name.
   * @param mixed $envName
   *   The environment name.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory stub.
   */
  protected function getConfigStub(mixed $projectName = NULL, mixed $envName = NULL) :  ConfigFactoryInterface {
    $config = [];

    if ($projectName) {
      $config[EnvironmentResolver::PROJECT_NAME_KEY] = $projectName;
    }
    if ($envName) {
      if (!$envName instanceof EnvironmentEnum) {
        $envName = EnvironmentEnum::tryFrom($envName);
      }
      $config[EnvironmentResolver::ENVIRONMENT_NAME_KEY] = $envName->value;
    }

    if ($this instanceof UnitTestCase) {
      /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
      $configFactory = $this->getConfigFactoryStub([
        'helfi_api_base.environment_resolver.settings' => $config,
      ]);
      return $configFactory;
    }
    $this->setActiveProject($projectName, $envName);

    return $this->container->get('config.factory');
  }

  /**
   * Gets the environment resolver.
   *
   * @return \Drupal\helfi_api_base\Environment\EnvironmentResolver
   *   The sut.
   */
  protected function getEnvironmentResolver(mixed $projectName = NULL, mixed $envName = NULL) : EnvironmentResolver {
    $configStub = $this->getConfigStub($projectName, $envName);
    return new EnvironmentResolver($configStub);
  }

}

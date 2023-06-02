<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests environment resolver.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\EnvironmentResolver
 * @group helfi_api_base
 */
class EnvironmentResolverTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * @covers ::populateActiveProjectSettings
   * @covers ::populateEnvironments
   * @covers ::__construct
   */
  public function testPopulateActiveProjectSettings() : void {
    $sut = new EnvironmentResolver('', $this->container->get('config.factory'));
    $sut->populateActiveProjectSettings(Project::ASUMINEN, EnvironmentEnum::Test);

    $config = $this->config('helfi_api_base.environment_resolver.settings');
    $this->assertEquals(Project::ASUMINEN, $config->get(EnvironmentResolver::PROJECT_NAME_KEY));
    $this->assertEquals(EnvironmentEnum::Test->value, $config->get(EnvironmentResolver::ENVIRONMENT_NAME_KEY));
  }

}

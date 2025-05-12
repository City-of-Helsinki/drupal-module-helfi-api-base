<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Tests\UnitTestCase;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Drupal\helfi_api_base\Environment\ActiveProjectRoles;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\helfi_api_base\Environment\ProjectRoleEnum;

/**
 * Tests ActiveProjectType.
 *
 * @group helfi_api_base
 */
class ActiveProjectRolesTest extends UnitTestCase {

  use EnvironmentResolverTrait;

  /**
   * Tests ::isCoreInstance().
   *
   * @dataProvider isCoreInstanceData
   */
  public function testIsCoreInstance(bool $expected, ?string $projectName, ?EnvironmentEnum $env): void {
    $sut = new ActiveProjectRoles($this->getEnvironmentResolver($projectName, $env));
    $this->assertEquals($expected, $sut->hasRole(ProjectRoleEnum::Core));
  }

  /**
   * A data provider.
   *
   * @return array[]
   *   The data.
   */
  public function isCoreInstanceData(): array {
    return [
      [FALSE, NULL, NULL],
      [TRUE, Project::ASUMINEN, EnvironmentEnum::Local],
      [FALSE, 'non-existent', NULL],
      [FALSE, Project::PAATOKSET, EnvironmentEnum::Prod],
    ];
  }

}

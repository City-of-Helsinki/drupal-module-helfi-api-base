<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Tests\UnitTestCase;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Drupal\helfi_api_base\Environment\ActiveProjectRoles;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\helfi_api_base\Environment\ProjectRoleEnum;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests ActiveProjectType.
 *
 * @group helfi_api_base
 */
class ActiveProjectRolesTest extends UnitTestCase {

  use EnvironmentResolverTrait;

  /**
   * Tests ::isCoreInstance().
   */
  #[DataProvider(methodName: 'isCoreInstanceData')]
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
  public static function isCoreInstanceData(): array {
    return [
      [FALSE, NULL, NULL],
      [TRUE, Project::ASUMINEN, EnvironmentEnum::Local],
      [FALSE, 'non-existent', NULL],
      [FALSE, Project::PAATOKSET, EnvironmentEnum::Prod],
      [TRUE, Project::ETUSIVU, EnvironmentEnum::Local],
    ];
  }

  /**
   * Tests the HasEtusivuIndex role.
   */
  #[DataProvider(methodName: 'hasEtusivuIndexData')]
  public function testHasEtusivuIndex(bool $expected, ?string $projectName, ?EnvironmentEnum $env): void {
    $sut = new ActiveProjectRoles($this->getEnvironmentResolver($projectName, $env));
    $this->assertEquals($expected, $sut->hasRole(ProjectRoleEnum::HasEtusivuIndex));
  }

  /**
   * A data provider.
   *
   * @return array[]
   *   The data.
   */
  public static function hasEtusivuIndexData(): array {
    return [
      [FALSE, NULL, NULL],
      [TRUE, Project::ASUMINEN, EnvironmentEnum::Local],
      [FALSE, 'non-existent', NULL],
      [TRUE, Project::PAATOKSET, EnvironmentEnum::Prod],
      [TRUE, Project::ETUSIVU, EnvironmentEnum::Local],
      [FALSE, Project::GRANTS, EnvironmentEnum::Local],
    ];
  }

}

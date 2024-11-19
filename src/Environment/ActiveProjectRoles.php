<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A service to determine the type of currently active project.
 */
final readonly class ActiveProjectRoles {

  public function __construct(private EnvironmentResolverInterface $environmentResolver) {
  }

  /**
   * Checks if the current instance has the given role.
   *
   * @return bool
   *   TRUE if the project has given role.
   */
  public function hasRole(ProjectRoleEnum $role): bool {
    return in_array($role, $this->getRoles());
  }

  /**
   * Gets the project features.
   *
   * @return array<\Drupal\helfi_api_base\Environment\ProjectRoleEnum>
   *   The features.
   */
  private function getRoles(): array {
    $roles = [];

    try {
      // Instance is considered as a "core" if it's included in main-navigation
      // structure, so basically all Drupal instances under www.hel.fi domain.
      //
      // Currently, only core instances are defined in EnvironmentResolver, so
      // we can use ::getActiveProject() to determine if the current instance is
      // a core instance.
      // @todo Include all instances in EnvironmentResolver and include
      // Project role data in Project object.
      $this->environmentResolver->getActiveProject();

      $roles[] = ProjectRoleEnum::Core;
    }
    catch (\InvalidArgumentException) {
    }
    return $roles;
  }

}

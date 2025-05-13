<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A service to determine the roles of currently active project.
 */
final class ActiveProjectRoles {

  public function __construct(private readonly EnvironmentResolverInterface $environmentResolver) {
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
    try {
      return $this->environmentResolver->getActiveProject()->roles;
    }
    catch (\InvalidArgumentException) {
    }

    return [];
  }

}

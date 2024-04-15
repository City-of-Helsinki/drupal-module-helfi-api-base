<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Features;

/**
 * Provides an interface for Feature manager.
 */
interface FeatureManagerInterface {

  public const LOGGER = 'logger';
  public const ROTATE_UID1_PASSWORD = 'rotate_uid1_password';

  /**
   * Checks whether the given feature is enabled or not.
   *
   * @param string $feature
   *   The feature name to check.
   *
   * @return bool
   *   TRUE if feature is enabled, FALSE if not.
   */
  public function isEnabled(string $feature): bool;

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Features;

/**
 * A service to manage different features.
 */
interface FeatureManagerInterface {

  public const USE_MOCK_RESPONSES = 'use_mock_responses';
  public const DISABLE_USER_PASSWORD = 'disable_user_password';
  public const USER_EXPIRE = 'user_expire';
  public const DISABLE_EMAIL_SENDING = 'disable_email_sending';

  /**
   * Enables the given feature.
   *
   * @param string $feature
   *   The feature to enable.
   */
  public function enableFeature(string $feature): void;

  /**
   * Disables the given feature.
   *
   * @param string $feature
   *   The feature to disable.
   */
  public function disableFeature(string $feature): void;

  /**
   * Gets all features.
   *
   * @return array<string, bool>
   *   The available features and their states.
   */
  public function getFeatures(): array;

  /**
   * Checks if the given feature is enabled.
   *
   * @param string $feature
   *   The feature to check.
   *
   * @return bool
   *   TRUE if enabled, FALSE otherwise.
   */
  public function isEnabled(string $feature): bool;

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Features;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a service to manage different features.
 */
final class FeatureManager {

  public const LOGGER = 'logger';
  public const DISABLE_USER_PASSWORD = 'disable_user_password';

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
  ) {
  }

  /**
   * Enables the given feature.
   *
   * @param string $feature
   *   The feature to enable.
   */
  public function enableFeature(string $feature) : void {
    $this->assertFeature($feature);

    $this->configFactory->getEditable('helfi_api_base.features')
      ->set($feature, TRUE)
      ->save();
  }

  /**
   * Checks if the given feature is valid.
   *
   * @param string $feature
   *   The feature to check.
   */
  private function assertFeature(string $feature) : void {
    $features = $this->getFeatures();

    if (!isset($features[$feature])) {
      throw new \InvalidArgumentException(sprintf('Invalid feature: "%s".', $feature));
    }
  }

  /**
   * Gets all features.
   *
   * @return array<string, boolean>
   *   The available features.
   */
  public function getFeatures() : array {
    $config = $this->configFactory->get('helfi_api_base.features');
    $constants = (new \ReflectionClass(__CLASS__))
      ->getConstants();

    $features = [];
    foreach ($constants as $value) {
      $features[$value] = (bool) $config->get($value);
    }
    return $features;
  }

  /**
   * Disables the given feature.
   *
   * @param string $feature
   *   The feature to disable.
   */
  public function disableFeature(string $feature) : void {
    $this->assertFeature($feature);

    $this->configFactory->getEditable('helfi_api_base.features')
      ->set($feature, FALSE)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(string $feature) : bool {
    $this->assertFeature($feature);
    $config = $this->configFactory->get('helfi_api_base.features');

    return (bool) $config->get($feature);
  }

}

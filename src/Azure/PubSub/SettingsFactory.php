<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * A factory to initialize Settings object.
 */
final class SettingsFactory {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory
  ) {
  }

  /**
   * Constructs a new PubSub settings object.
   *
   * @return \Drupal\helfi_api_base\Azure\PubSub\Settings
   *   The PubSub settings object.
   */
  public function create() : Settings {
    $config = $this->configFactory->get('helfi_api_base.pubsub.settings');

    return new Settings(
      $config->get('hub') ?: '',
      $config->get('group') ?: '',
      $config->get('endpoint') ?: '',
      $config->get('access_token') ?: '',
    );
  }

}

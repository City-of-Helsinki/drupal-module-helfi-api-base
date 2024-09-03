<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Drupal\helfi_api_base\Vault\VaultManager;

/**
 * A factory to initialize a Settings object.
 */
final class SettingsFactory {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Vault\VaultManager $vaultManager
   *   The vault manager.
   */
  public function __construct(
    private readonly VaultManager $vaultManager,
  ) {
  }

  /**
   * Constructs a new PubSub settings object.
   *
   * @return \Drupal\helfi_api_base\Azure\PubSub\Settings
   *   The PubSub settings object.
   */
  public function create() : Settings {
    if (!$settings = $this->vaultManager->get('pubsub')) {
      // Return an empty settings object in case PubSub is not
      // configured.
      return new Settings('', '', '', []);
    }
    $data = $settings->data();

    $accessKeys = [];
    foreach (['access_key', 'secondary_access_key'] as $key) {
      if (empty($data->{$key})) {
        continue;
      }
      $accessKeys[] = $data->{$key};
    }

    return new Settings(
      $data->hub ?: '',
      $data->group ?: '',
      $data->endpoint ?: '',
      $accessKeys,
    );
  }

}

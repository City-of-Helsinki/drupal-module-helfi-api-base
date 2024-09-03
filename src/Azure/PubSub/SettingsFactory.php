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
    $data = [
      'hub' => '',
      'group' => '',
      'endpoint' => '',
      'access_key' => '',
      'secondary_access_key' => '',
    ];

    if ($settings = $this->vaultManager->get('pubsub')) {
      foreach ($data as $key => $value) {
        if (!isset($settings->data()->{$key})) {
          continue;
        }
        $data[$key] = $settings->data()->{$key};
      }
    }
    $data = (object) $data;

    return new Settings(
      $data->hub ?: '',
      $data->group ?: '',
      $data->endpoint ?: '',
      $data->access_key ?: '',
      $data->secondary_access_key ?: '',
    );
  }

}

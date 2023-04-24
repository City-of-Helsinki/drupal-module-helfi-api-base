<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Vault;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * A factory class to initialize vault manager.
 */
final class VaultManagerFactory {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    private ConfigFactoryInterface $configFactory,
  ) {
  }

  /**
   * Constructs a new vault manager.
   *
   * @return \Drupal\helfi_api_base\Vault\VaultManager
   *   The vault manager.
   */
  public function create() : VaultManager {
    $config = $this->configFactory->get('helfi_api_base.api_accounts')
      ->get('vault') ?? [];

    $items = array_map(function (array $item) : VaultItemInterface {
      if (!isset($item['plugin'], $item['id'], $item['data'])) {
        throw new \InvalidArgumentException('Missing required "plugin", "id" or "data".');
      }
      return match($item['plugin']) {
        AuthorizationToken::PLUGIN => new AuthorizationToken($item['id'], $item['data']),
      };
    }, $config);

    return new VaultManager($items);
  }

}

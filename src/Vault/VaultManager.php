<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Vault;

use Webmozart\Assert\Assert;

/**
 * Vault manager can be used to manage secrets, such as authorization tokens.
 */
final class VaultManager {

  /**
   * The vault items.
   *
   * @var \Drupal\helfi_api_base\Vault\VaultItemInterface[]
   */
  private array $items;

  /**
   * Constructs a new instance.
   *
   * @param array $vaultItems
   *   The vault items.
   */
  public function __construct(
    array $vaultItems,
  ) {
    Assert::allIsInstanceOf($vaultItems, VaultItemInterface::class);

    foreach ($vaultItems as $item) {
      $this->items[$item->id()] = $item;
    }
  }

  /**
   * Gets the vault item.
   *
   * @param string $id
   *   The ID of the vault item.
   *
   * @return \Drupal\helfi_api_base\Vault\VaultItemInterface|null
   *   The vault item or null.
   */
  public function get(string $id) : ? VaultItemInterface {
    return $this->items[$id] ?? NULL;
  }

}

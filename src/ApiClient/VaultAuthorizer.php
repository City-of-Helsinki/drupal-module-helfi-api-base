<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ApiClient;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\helfi_api_base\Vault\VaultManager;

/**
 Performs HTTP basic authentication using credentials from VaultManager.
 */
final readonly class VaultAuthorizer implements ApiAuthorizerInterface {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service.
   * @param \Drupal\helfi_api_base\Vault\VaultManager $vaultManager
   *   The vault manager service.
   * @param string $vaultKey
   *   The ID of the vault item.
   * @param ?string $fallback
   *   BC: fetch authorization token from given config.
   */
  public function __construct(
    private ConfigFactoryInterface $configFactory,
    private VaultManager $vaultManager,
    private string $vaultKey,
    private ?string $fallback = NULL,
  ) {
  }

  /**
   * Gets the authorization value.
   *
   * @return string|null
   *    The authorization value.
   */
  public function getAuthorization(): ?string {
    if ($token = $this->getToken()) {
      return sprintf('Basic %s', $token);
    }

    return NULL;
  }

  /**
   * Gets the authorization token.
   *
   * @return string|null
   *   The authorization token.
   */
  private function getToken() : ?string {
    if ($authorization = $this->vaultManager->get($this->vaultKey)) {
      return $authorization->data();
    }

    // Provide a BC layer to fetch API keys from previously used
    // configuration.
    // @todo remove this once all projects have migrated to Vault.
    if ($this->fallback) {
      [$config, $key] = explode(':', $this->fallback);

      return $this->configFactory->get($config)?->get($key);
    }

    return NULL;
  }

}

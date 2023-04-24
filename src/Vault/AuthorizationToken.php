<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Vault;

/**
 * A value object to store authorization token vault item.
 */
final class AuthorizationToken implements VaultItemInterface {

  public const PLUGIN = 'authorization_token';

  /**
   * Constructs a new instance.
   *
   * @param string $id
   *   The ID.
   * @param string $token
   *   The authorization token.
   */
  public function __construct(
    private readonly string $id,
    private readonly string $token,
  ) {
  }

  /**
   * Gets the id.
   *
   * @return string
   *   The ID.
   */
  public function id() : string {
    return $this->id;
  }

  /**
   * Gets the data.
   *
   * @return string
   *   The data.
   */
  public function data() : string {
    return $this->token;
  }

}

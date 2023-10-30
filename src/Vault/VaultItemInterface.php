<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Vault;

/**
 * An interface to represent vault item value objects.
 */
interface VaultItemInterface {

  public const PLUGIN = '';

  /**
   * Gets the id.
   *
   * @return string
   *   The ID.
   */
  public function id() : string;

  /**
   * Gets the data.
   *
   * @return mixed
   *   The data.
   */
  public function data() : mixed;

}

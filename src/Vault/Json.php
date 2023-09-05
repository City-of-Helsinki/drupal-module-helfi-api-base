<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Vault;

/**
 * A value object to store string item vault items.
 */
final class Json implements VaultItemInterface {

  public const PLUGIN = 'json';

  /**
   * The json decoded data.
   *
   * @var object|mixed
   */
  private readonly object $data;

  /**
   * Constructs a new instance.
   *
   * @param string $id
   *   The ID.
   * @param string $string
   *   The JSON string.
   *
   * @throws \JsonException
   */
  public function __construct(
    private readonly string $id,
    string $string,
  ) {
    $this->data = json_decode($string, flags: JSON_THROW_ON_ERROR);
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
   * @return object
   *   The data.
   */
  public function data() : object {
    return $this->data;
  }

}

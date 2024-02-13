<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

/**
 * A DTO representing PubSub settings.
 */
final class Settings {

  /**
   * Constructs a new instance.
   *
   * @param string $hub
   *   The hub.
   * @param string $group
   *   The group.
   * @param string $endpoint
   *   The API endpoint.
   * @param string $accessKey
   *   The API access token.
   */
  public function __construct(
    public readonly string $hub,
    public readonly string $group,
    public readonly string $endpoint,
    public readonly string $accessKey,
  ) {
  }

}

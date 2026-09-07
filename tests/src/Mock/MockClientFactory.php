<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Mock;

use Drupal\Core\Http\ClientFactory;
use GuzzleHttp\Client;

/**
 * A client factory that always returns the given client.
 */
final class MockClientFactory extends ClientFactory {

  public function __construct(private readonly Client $client) {
  }

  /**
   * {@inheritDoc}
   *
   * @param array<string, mixed> $config
   *   The client configuration.
   */
  public function fromOptions(array $config = []) : Client {
    return $this->client;
  }

}

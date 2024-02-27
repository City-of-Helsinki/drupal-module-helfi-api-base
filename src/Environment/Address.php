<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A value object to store address information.
 */
final class Address {

  /**
   * Constructs a new instance.
   *
   * @param string $domain
   *   The domain.
   * @param string $protocol
   *   The protocol.
   * @param int $port
   *   The port.
   */
  public function __construct(
    public readonly string $domain,
    public readonly string $protocol = 'https',
    public readonly int $port = 443,
  ) {
  }

  /**
   * Gets the address.
   *
   * @return string
   *   The address.
   */
  public function getAddress() : string {
    $port = '';
    if (!in_array($this->port, [80, 443])) {
      $port = sprintf(':%d', $this->port);
    }
    return sprintf('%s://%s%s', $this->protocol, $this->domain, $port);
  }

}

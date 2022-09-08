<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Link;

use Drupal\Core\Url;

/**
 * Resolves internal domains.
 */
final class InternalDomainResolver {

  /**
   * Constructs a new instance.
   *
   * @param array $domains
   *   The domains.
   */
  public function __construct(private array $domains = []) {
  }

  /**
   * Gets an array of domains considered as an 'internal'.
   *
   * These can be configured by overriding the
   * 'helfi_api_base.internal_domains' parameter in services.yml file.
   *
   * @return array
   *   The domains.
   */
  public function getDomains() : array {
    return $this->domains;
  }

  /**
   * Gets the url protocol.
   *
   * @param \Drupal\Core\Url $url
   *   The URL.
   *
   * @return string|null
   *   The protocol.
   */
  public function getProtocol(Url $url) : ? string {
    if (!$url->isExternal()) {
      return NULL;
    }
    $scheme = parse_url($url->getUri(), PHP_URL_SCHEME);

    // Skip generic schemes since we're not interested in them.
    return !in_array($scheme, ['http', 'https']) ? $scheme : NULL;
  }

  /**
   * Checks if the given URL is external.
   *
   * This is used to whitelist certain domains as internal.
   *
   * @return bool
   *   TRUE if the url is external.
   */
  public function isExternal(Url $url) : bool {
    if (!$url->isExternal()) {
      return FALSE;
    }

    if (!$host = parse_url($url->getUri(), PHP_URL_HOST)) {
      return TRUE;
    }

    $isExternal = TRUE;
    foreach ($this->getDomains() as $domain) {
      if (
        // Support wildcard domains (*.docker.so for example).
        (str_starts_with($domain, '*.') && str_ends_with($host, substr($domain, 2))) ||
        $domain === $host
      ) {
        $isExternal = FALSE;
        break;
      }
    }
    return $isExternal;
  }

}

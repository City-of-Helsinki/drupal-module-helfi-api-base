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

    $host = parse_url($url->getUri(), PHP_URL_HOST);

    foreach ($this->getDomains() as $domain) {
      if (
        // Support wildcard domains (*.docker.so for example).
        (str_starts_with($domain, '*.') && str_ends_with($host, substr($domain, 2))) ||
        $domain === $host
      ) {
        return FALSE;
      }
    }
    return TRUE;
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Helper;

use Drupal\Core\Url;

/**
 * Helper class for dealing with external URIs.
 */
final class ExternalUri {

  public const WHITELIST = [
    'www.hel.fi',
    'paatokset.hel.fi',
    'avustukset.hel.fi',
  ];
  public const WHITELIST_OPTION_KEY = 'whitelisted_external_url';

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Url $url
   *   The URL object.
   */
  public function __construct(private Url $url) {
  }

  /**
   * Checks if the given URL is external.
   *
   * This is used to whitelist certain domains as internal.
   *
   * @return bool
   *   TRUE if the url is external.
   */
  public function isExternal() : bool {
    if (!$this->url->isExternal()) {
      return FALSE;
    }

    // Allow links with whitelisted host to act as an internal.
    return !in_array(
      parse_url($this->url->getUri(), PHP_URL_HOST),
      self::WHITELIST
    );
  }

}

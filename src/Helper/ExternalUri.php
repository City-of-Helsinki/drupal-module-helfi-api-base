<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Helper;

use Drupal\Core\Url;

/**
 * Helper class for dealing with external URIs.
 */
final class ExternalUri {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Url $url
   *   The URL object.
   * @param array $whitelist
   *   The whitelist.
   */
  public function __construct(
    private Url $url,
    private array $whitelist
  ) {
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

    // Allow whitelisted links to act as an internal.
    return !in_array(
      parse_url($this->url->getUri(), PHP_URL_HOST),
      $this->whitelist
    );
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Link;

use Drupal\Core\Url;

/**
 * Helper class for dealing with URLs.
 */
final class UrlHelper {

  /**
   * Parses the embedded url.
   *
   * @param string $value
   *   The url.
   *
   * @return \Drupal\Core\Url
   *   The URL.
   */
  public static function parse(string $value) : Url {
    if (str_starts_with($value, '/') || str_starts_with($value, '#')) {
      return Url::fromUserInput($value);
    }

    try {
      return Url::fromUri($value);
    }
    catch (\InvalidArgumentException) {
      // Default to https://{value} if previous attempt failed.
      // If this fails too, the result should be logged.
      return Url::fromUri(sprintf('https://%s', $value));
    }
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ApiClient;

/**
 * Handle API authorization.
 */
interface ApiAuthorizerInterface {

  /**
   * Gets the authorization header value.
   *
   * @return string|null
   *    The authorization header value.
   */
  public function getAuthorization(): ?string;

}

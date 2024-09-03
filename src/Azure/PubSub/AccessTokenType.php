<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

/**
 * The access token type.
 */
enum AccessTokenType {
  case Primary;
  case Secondary;
}

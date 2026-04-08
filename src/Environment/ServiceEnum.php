<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * The service enum.
 */
enum ServiceEnum: string {
  // Internal address for Elastic proxy service.
  case ElasticProxy = 'elastic-proxy';
  // Browser accessible address for Elastic proxy service.
  case PublicElasticProxy = 'public-elastic-proxy';
}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A helper trait to deal with environments.
 */
trait EnvironmentTrait {

  /**
   * A mapping function to match APP_ENV with environment resolver.
   *
   * @param string $environment
   *   APP_ENV or environment name.
   *
   * @return null|string
   *   The environment name.
   */
  protected function normalizeEnvironmentName(string $environment) : ? string {
    $environment = strtolower($environment);
    // Some environments have an incorrect APP_ENV value, like 'production',
    // 'staging' and 'testing' instead of 'local', 'test,' 'stage' and 'prod'.
    // Map all known environment name variations to match environment resolver.
    $environments = [
      'development' => 'dev',
      'testing' => 'test',
      'staging' => 'stage',
      'production' => 'prod',
      // Make sure CI resolves to a known environment.
      'ci' => 'test',
    ];

    if (array_key_exists($environment, $environments)) {
      return $environments[$environment];
    }
    return $environment;
  }

}

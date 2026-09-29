<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * Provides helpers to access services of the active environment.
 *
 * Classes using this trait must have an $environmentResolver property that
 * holds the environment resolver.
 *
 * @property \Drupal\helfi_api_base\Environment\EnvironmentResolverInterface $environmentResolver
 */
trait ActiveServiceTrait {

  /**
   * Gets the browser accessible Elastic proxy of the active environment.
   *
   * @return \Drupal\helfi_api_base\Environment\Address|null
   *   The Elastic proxy address, or NULL if the active project or environment
   *   can't be resolved or has no Elastic proxy.
   */
  protected function getPublicElasticProxy(): ?Address {
    try {
      return $this->environmentResolver
        ->getActiveEnvironment()
        ->getService(ServiceEnum::PublicElasticProxy)
        ?->address;
    }
    catch (EnvironmentResolverException) {
      return NULL;
    }
  }

}

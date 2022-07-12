<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Link;

use Drupal\Core\Url;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;

/**
 * Resolves internal domains.
 */
final class InternalDomainResolver {

  /**
   * Constructs a new instance.
   *
   * @param array $domains
   *   The domains.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentResolver $environmentResolver
   *   The environment resolver.
   */
  public function __construct(
    protected EnvironmentResolver $environmentResolver,
    private array $domains = []
  ) {}

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
    $domains = [];

    if ($env = getenv('APP_ENV')) {
      foreach ($this->environmentResolver->getProjects() as $project) {
        if (array_key_exists($env, $project)) {
          $domains[] = $project[$env]->getDomain();
        }
      }
    }

    return $this->domains + $domains;
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

    // Allow whitelisted links to act as an internal.
    return !in_array(
      parse_url($url->getUri(), PHP_URL_HOST),
      $this->getDomains()
    );
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A value object to store environment data.
 */
final class Environment {

  /**
   * Constructs a new instance.
   *
   * @param string $domain
   *   The domain.
   * @param array $paths
   *   The paths.
   */
  public function __construct(
    private string $domain,
    private array $paths
  ) {
  }

  /**
   * Gets the domain.
   *
   * @return string
   *   The domain.
   */
  public function getDomain() : string {
    return $this->domain;
  }

  /**
   * Gets the path.
   *
   * @param string $language
   *   The language.
   *
   * @return string
   *   The path.
   */
  public function getPath(string $language) : string {
    if (!isset($this->paths[$language])) {
      throw new \InvalidArgumentException(
        sprintf('Path not found for "%s" language.', $language)
      );
    }
    return $this->paths[$language];
  }

}

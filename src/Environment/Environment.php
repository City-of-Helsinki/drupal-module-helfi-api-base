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
   * @param string $protocol
   *   The protocol.
   */
  public function __construct(
    private string $domain,
    private array $paths,
    private string $protocol
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
   * Gets the protocol.
   *
   * @return string
   *   The protocol.
   */
  public function getProtocol() : string {
    return $this->protocol;
  }

  /**
   * Gets the base URL for given language.
   *
   * @param string $language
   *   The language.
   *
   * @return string
   *   The URL.
   */
  public function getUrl(string $language) : string {
    return vsprintf('%s/%s', [
      $this->getBaseUrl(),
      ltrim($this->getPath($language), '/'),
    ]);
  }

  /**
   * Gets the base url.
   *
   * @return string
   *   The base url.
   */
  public function getBaseUrl() : string {
    return vsprintf('%s://%s', [
      $this->getProtocol(),
      $this->getDomain(),
    ]);
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

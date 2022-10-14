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
   * @param string $id
   *   Environment resolver identifier for the project.
   * @param string $environmentName
   *   The environment name.
   */
  public function __construct(
    private string $domain,
    private array $paths,
    private string $protocol,
    private string $id,
    private string $environmentName
  ) {
  }

  /**
   * Gets the project identifier.
   *
   * @return string
   *   Site identifier.
   */
  public function getId() : string {
    return $this->id;
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
   * Gets the original URL for given language.
   *
   * @param string $language
   *   The language.
   *
   * @return string
   *   The URL.
   */
  private function doGetUrl(string $language) : string {
    return vsprintf('%s/%s', [
      $this->getBaseUrl(),
      ltrim($this->getPath($language), '/'),
    ]);
  }

  /**
   * Gets the full URL for given language.
   *
   * @param string $language
   *   The language.
   *
   * @return string
   *   The URL.
   */
  public function getUrl(string $language) : string {
    $url = $this->doGetUrl($language);
    // Local uses an internal address by default to allow containers to
    // communicate via API requests. Convert URL back to a proper link that works
    // with browsers.
    return str_replace(['http://', ':8080'], ['https://', ''], $url);
  }

  /**
   * Gets the canonical URL for given language.
   *
   * @param string $language
   *   The language.
   *
   * @return string
   *   The canonical URL.
   */
  public function getInternalAddress(string $language) : string {
    return $this->doGetUrl($language);
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

  /**
   * Gets the environment name.
   *
   * @return string
   *   The environment.
   */
  public function getEnvironmentName(): string {
    return $this->environmentName;
  }

}

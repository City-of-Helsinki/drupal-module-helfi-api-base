<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A value object to store environment data.
 */
final class Environment {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Environment\Address $address
   *   The address.
   * @param \Drupal\helfi_api_base\Environment\Address $internalAddress
   *   The internal address.
   * @param array $paths
   *   The paths.
   * @param string $id
   *   Environment resolver identifier for the project.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentEnum $environment
   *   The environment name.
   * @param \Drupal\helfi_api_base\Environment\Service|null $services
   *   The environment services.
   */
  public function __construct(
    public readonly Address $address,
    public readonly Address $internalAddress,
    public readonly array $paths,
    public readonly string $id,
    public readonly EnvironmentEnum $environment,
    public readonly array $services = [],
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
   * Gets the full URL for given language.
   *
   * @param string $language
   *   The language.
   *
   * @return string
   *   The URL.
   */
  public function getUrl(string $language) : string {
    return sprintf('%s/%s', $this->address->getAddress(), ltrim($this->getPath($language), '/'));
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
    return sprintf('%s/%s', $this->internalAddress->getAddress(), ltrim($this->getPath($language), '/'));
  }

  /**
   * Gets the base url.
   *
   * @return string
   *   The base url.
   */
  public function getBaseUrl() : string {
    return $this->address->getAddress();
  }

  /**
   * Gets the internal base url.
   *
   * @return string
   *   The base url.
   */
  public function getInternalBaseUrl() : string {
    return $this->internalAddress->getAddress();
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
    return $this->environment->value;
  }

  /**
   * Gets the environment mapping.
   *
   * @return \Drupal\helfi_api_base\Environment\EnvironmentEnum
   *   The environment mapping.
   */
  public function getEnvironment() : EnvironmentEnum {
    return $this->environment;
  }

}

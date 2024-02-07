<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

use Drupal\helfi_api_base\Exception\EnvironmentException;

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
   * @param \Drupal\helfi_api_base\Environment\EnvironmentMetadata|null $metadata
   *   The environment specific metadata.
   */
  public function __construct(
    private readonly Address $address,
    private readonly Address $internalAddress,
    private readonly array $paths,
    private readonly string $id,
    private readonly EnvironmentEnum $environment,
    private readonly ?EnvironmentMetadata $metadata,
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
      throw new EnvironmentException(
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

  /**
   * Gets the environment metadata.
   *
   * @return \Drupal\helfi_api_base\Environment\EnvironmentMetadata|null
   *   The metadata.
   */
  public function getMetadata(): ?EnvironmentMetadata {
    return $this->metadata;
  }

}

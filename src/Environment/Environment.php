<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

use Webmozart\Assert\Assert;

/**
 * A value object to store environment data.
 */
final class Environment {

  /**
   * The services.
   *
   * @var \Drupal\helfi_api_base\Environment\Service[]
   */
  private array $services;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Environment\Address $address
   *   The address.
   * @param \Drupal\helfi_api_base\Environment\Address $internalAddress
   *   The internal address.
   * @param array $paths
   *   The paths.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentEnum $environment
   *   The environment name.
   * @param \Drupal\helfi_api_base\Environment\Service[] $services
   *   The environment services.
   */
  public function __construct(
    public readonly Address $address,
    public readonly Address $internalAddress,
    public readonly array $paths,
    public readonly EnvironmentEnum $environment,
    array $services = [],
  ) {
    Assert::allIsInstanceOf($services, Service::class);

    foreach ($services as $service) {
      $this->services[$service->name] = $service;
    }
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

  /**
   * Gets the services.
   *
   * @return \Drupal\helfi_api_base\Environment\Service[]
   *   The services.
   */
  public function getServices(): array {
    return $this->services;
  }

  /**
   * Gets the given service.
   *
   * @param string $name
   *   The service to get.
   *
   * @return \Drupal\helfi_api_base\Environment\Service|null
   *   The service or null.
   */
  public function getService(string $name): ?Service {
    return $this->services[$name] ?? NULL;
  }

}

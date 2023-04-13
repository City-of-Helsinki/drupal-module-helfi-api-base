<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

use Webmozart\Assert\Assert;

/**
 * A value object to store all projects.
 */
final class Project {

  use EnvironmentTrait;

  public const ASUMINEN = 'asuminen';
  public const ETUSIVU = 'etusivu';
  public const KASVATUS_KOULUTUS = 'kasvatus-koulutus';
  public const KUVA = 'kuva';
  public const LIIKENNE = 'liikenne';
  public const REKRY = 'rekry';
  public const STRATEGIA = 'strategia';
  public const TERVEYS = 'terveys';
  public const TYO_YRITTAMINEN = 'tyo-yrittaminen';

  /**
   * The environments.
   *
   * @var \Drupal\helfi_api_base\Environment\Environment[]
   */
  private array $environments;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Environment\Environment[] $environments
   *   The environments.
   */
  public function __construct(array $environments = []) {
    Assert::allIsInstanceOf($environments, Environment::class);
    $this->environments = $environments;
  }

  /**
   * Gets all environments.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment[]
   *   A list of environments.
   */
  public function getEnvironments() : array {
    return $this->environments;
  }

  /**
   * Adds an environment.
   *
   * @param string $key
   *   The environment key.
   * @param \Drupal\helfi_api_base\Environment\Environment $environment
   *   The environment.
   *
   * @return $this
   *   The self.
   */
  public function addEnvironment(string $key, Environment $environment) : self {
    $this->environments[$key] = $environment;
    return $this;
  }

  /**
   * Checks if environment exists.
   *
   * @param string $environment
   *   The environment key.
   *
   * @return bool
   *   TRUE if environment exists.
   */
  public function hasEnvironment(string $environment) : bool {
    $environment = $this->normalizeEnvironmentName($environment);

    return isset($this->environments[$environment]);
  }

  /**
   * Gets the given environment.
   *
   * @param string $environment
   *   The environment name.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment
   *   The environment.
   */
  public function getEnvironment(string $environment) : Environment {
    $environment = $this->normalizeEnvironmentName($environment);

    if (!$this->hasEnvironment($environment)) {
      throw new \InvalidArgumentException(sprintf('Environment "%s" not found.', $environment));
    }
    return $this->environments[$environment];
  }

}

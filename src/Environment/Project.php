<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

use Webmozart\Assert\Assert;

/**
 * A value object to store all projects.
 */
final class Project {

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
   * Gets the given environment.
   *
   * @param string $environment
   *   The environment name.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment
   *   The environment.
   */
  public function getEnvironment(string $environment) : Environment {
    $environment = $this->mapEnvironmentName($environment);

    if (!isset($this->environments[$environment])) {
      throw new \InvalidArgumentException(sprintf('Environment "%s" not found.', $environment));
    }
    return $this->environments[$environment];
  }

  /**
   * Temporary mapping function to match APP_ENV with environment resolver.
   *
   * @param string $environment
   *   APP_ENV or environment name.
   *
   * @return null|string
   *   The environment name.
   */
  private function mapEnvironmentName(string $environment) : ? string {
    // APP_ENV uses 'production', 'staging', 'testing' and 'development' as
    // a name, while environment resolver uses 'local', 'dev', 'test,' 'stage'
    // and 'prod'.
    // Map all known environment name variations to match environment resolver.
    $environments = [
      'devel' => 'dev',
      'development' => 'dev',
      'testing' => 'test',
      'staging' => 'stage',
      'production' => 'prod',
    ];

    if (array_key_exists($environment, $environments)) {
      return $environments[$environment];
    }
    return $environment;
  }

}

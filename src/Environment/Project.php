<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Webmozart\Assert\Assert;

/**
 * A value object of a project.
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
   * @param string $name
   *   The project name.
   * @param \Drupal\helfi_api_base\Environment\ProjectMetadata $metadata
   *   The metadata.
   * @param \Drupal\helfi_api_base\Environment\Environment[] $environments
   *   The environments.
   */
  public function __construct(
    private readonly string $name,
    private readonly ProjectMetadata $metadata,
    array $environments = []
  ) {
    Assert::allIsInstanceOf($environments, Environment::class);
    $this->environments = $environments;
  }

  /**
   * Gets the project machine name.
   *
   * @return string
   *   The project name.
   */
  public function getName() : string {
    return $this->name;
  }

  /**
   * Gets the project metadata.
   *
   * @return \Drupal\helfi_api_base\Environment\ProjectMetadata
   *   The metadata.
   */
  public function getMetadata() : ProjectMetadata {
    return $this->metadata;
  }

  /**
   * Gets the project label.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The label.
   */
  public function label() : TranslatableMarkup {
    return match ($this->name) {
      self::ASUMINEN => new TranslatableMarkup('Housing'),
      self::ETUSIVU => new TranslatableMarkup('Frontpage'),
      self::KASVATUS_KOULUTUS => new TranslatableMarkup('Childhood and education'),
      self::KUVA => new TranslatableMarkup('Culture and leisure'),
      self::LIIKENNE => new TranslatableMarkup('Urban environment and traffic'),
      self::REKRY => new TranslatableMarkup('Open jobs'),
      self::STRATEGIA => new TranslatableMarkup('Decision-making'),
      self::TERVEYS => new TranslatableMarkup('Health and social services'),
      self::TYO_YRITTAMINEN => new TranslatableMarkup('Business and work'),
    };
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

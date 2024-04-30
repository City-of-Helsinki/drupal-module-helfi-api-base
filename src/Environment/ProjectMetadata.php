<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A value object to store project metadata.
 */
final class ProjectMetadata {

  /**
   * The repository.
   *
   * @var string
   */
  public readonly string $repository;

  /**
   * Constructs a new instance.
   *
   * @param string $repositoryUrl
   *   The repository url.
   */
  public function __construct(
    private readonly string $repositoryUrl,
  ) {
    if (!$path = parse_url($this->repositoryUrl, PHP_URL_PATH)) {
      throw new \InvalidArgumentException('The repositoryUrl must be a valid URL.');
    }
    $this->repository = ltrim($path, '/');
  }

  /**
   * Gets the repository name.
   *
   * @return string
   *   The repository.
   */
  public function getRepository() : string {
    return $this->repository;
  }

  /**
   * Gets the normalized repository name.
   *
   * @return string
   *   The normalized repository name.
   */
  public function getNormalizedRepository() : string {
    return strtolower($this->repository);
  }

  /**
   * Gets the URL to repository.
   *
   * @return string
   *   The repository URL.
   */
  public function getRepositoryUrl() : string {
    return $this->repositoryUrl;
  }

}

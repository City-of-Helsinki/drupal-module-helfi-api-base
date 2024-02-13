<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A value object to store project metadata.
 */
final class ProjectMetadata {

  /**
   * Constructs a new instance.
   *
   * @param string $repository
   *   The repository.
   * @param string $azureDevopsLink
   *   The azure devops link.
   */
  public function __construct(
    private readonly string $repository,
    private readonly string $azureDevopsLink,
  ) {
  }

  /**
   * Construct a new instance from array.
   *
   * @param array $data
   *   The data.
   *
   * @return self
   *   The
   */
  public static function createFromArray(array $data) : self {
    $required = [
      'repository',
      'azure_devops_link',
    ];

    foreach ($required as $key) {
      if (!isset($data[$key])) {
        throw new \InvalidArgumentException(sprintf('Missing required "%s".', $key));
      }
    }

    [
      'repository' => $repository,
      'azure_devops_link' => $devopsLink,
    ] = $data;

    return new self($repository, $devopsLink);
  }

  /**
   * Gets the Azure DevOps link.
   *
   * @return string
   *   The azure_devops_link link.
   */
  public function getAzureDevopsLink() : string {
    return $this->azureDevopsLink;
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
    return sprintf('https://github.com/%s', $this->repository);
  }

}

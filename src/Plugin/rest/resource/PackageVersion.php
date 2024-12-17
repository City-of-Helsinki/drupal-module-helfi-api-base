<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Represents Package version as resources.
 *
 * @todo Remove this once the REST configuration is removed from all projects.
 *
 * @RestResource(
 *   id = "helfi_debug_package_version",
 *   label = @Translation("Package version"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/package"
 *   }
 * )
 */
final class PackageVersion extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get() : ResourceResponse {
    throw new BadRequestHttpException(sprintf('Deprecated'));
  }

}

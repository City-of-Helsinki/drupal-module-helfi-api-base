<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Represents Debug records as resources.
 *
 * @todo Remove this once the REST configuration is removed from all projects.
 *
 * @RestResource (
 *   id = "helfi_debug_data",
 *   label = @Translation("Debug data"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/debug"
 *   }
 * )
 */
final class DebugDataResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get() : ResourceResponse {
    return new ResourceResponse([]);
  }

}

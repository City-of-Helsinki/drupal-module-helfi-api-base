<?php

namespace Drupal\helfi_api_base\ApiClient;

use Drupal\Core\File\Exception\FileNotExistsException;
use GuzzleHttp\Utils;

/**
 * Helper class for mocking api responses.
 */
final class ApiFixture {

  /**
   * Get response from fixture file.
   *
   * @param string $fileName
   *   Fixture file.
   *
   * @return ApiResponse
   *   Mocked response.
   */
  public static function requestFromFile(string $fileName): ApiResponse {
    if (!file_exists($fileName)) {
      throw new FileNotExistsException(
        sprintf('The mock file "%s" was not found.', basename($fileName))
      );
    }

    return new ApiResponse(Utils::jsonDecode(file_get_contents($fileName)));
  }

}

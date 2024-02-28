<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Fixture;

/**
 * A base service to provide fixture data for migrations.
 */
abstract class FixtureBase {

  /**
   * Gets the responses used to mock API responses.
   *
   * @return \GuzzleHttp\Psr7\Response[]
   *   An array of responses to mock.
   */
  abstract public function getMockResponses() : array;

  /**
   * Gets the migrate configuration.
   *
   * @return array
   *   The configuration.
   */
  public function getConfiguration() : array {
    return [];
  }

}

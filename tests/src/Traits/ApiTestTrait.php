<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Provides shared functionality for api tests.
 */
trait ApiTestTrait {

  /**
   * Creates HTTP client stub.
   *
   * @param \Psr\Http\Message\ResponseInterface[] $responses
   *   The expected responses.
   *
   * @return \GuzzleHttp\Client
   *   The client.
   */
  protected function createMockHttpClient(array $responses) : Client {
    $mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($mock);

    return new Client(['handler' => $handlerStack]);
  }

  /**
   * Gets the fixture path.
   *
   * @param string $module
   *   The module.
   * @param string $name
   *   The name.
   *
   * @return string
   *   The fixture path.
   */
  protected function getFixturePath(string $module, string $name) : string {
    $file = sprintf('%s/tests/fixtures/%s', drupal_get_path('module', $module), $name);

    if (!file_exists($file)) {
      throw new \InvalidArgumentException(sprintf('Fixture %s not found', $name));
    }

    return $file;
  }

  /**
   * Gets the fixture data.
   *
   * @param string $module
   *   The module.
   * @param string $name
   *   The fixture name.
   *
   * @return string
   *   The fixture.
   */
  protected function getFixture(string $module, string $name) : string {
    return file_get_contents($this->getFixturePath($module, $name));
  }

}

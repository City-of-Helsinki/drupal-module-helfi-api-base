<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\Core\Extension\ExtensionPathResolver;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 * Provides shared functionality for api tests.
 */
trait ApiTestTrait {

  /**
   * Creates HTTP client stub.
   *
   * @param \Psr\Http\Message\ResponseInterface[]|\GuzzleHttp\Exception\GuzzleException[] $responses
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
   * Creates HTTP history middleware client stub.
   *
   * @param array $container
   *   The container.
   *
   * @return \GuzzleHttp\Client
   *   The client.
   */
  protected function createMockHistoryMiddlewareHttpClient(array &$container, array $responses = []) : Client {
    $history = Middleware::history($container);
    $mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push($history);

    return new Client(['handler' => $handlerStack]);
  }

  /**
   * Enable translation for given entity types.
   *
   * @param array $entityTypes
   *   The entity types to enable translation for.
   */
  protected function enableTranslation(array $entityTypes) : void {
    foreach ($entityTypes as $type) {
      \Drupal::service('content_translation.manager')->setEnabled($type, $type, TRUE);
    }
  }

  /**
   * Gets the extension path resolver.
   *
   * @return \Drupal\Core\Extension\ExtensionPathResolver
   *   The extension path resolver.
   */
  protected function getExtensionPathResolver() : ExtensionPathResolver {
    return $this->container->get('extension.path.resolver');
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
    $file = sprintf('%s/tests/fixtures/%s', $this->getExtensionPathResolver()->getPath('module', $module), $name);

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

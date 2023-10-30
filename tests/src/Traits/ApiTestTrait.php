<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Http\ClientFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides shared functionality for api tests.
 */
trait ApiTestTrait {

  /**
   * The container.
   *
   * @var \Drupal\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

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
   * Overrides the default 'http_client_factory' service with mock.
   *
   * @param \Psr\Http\Message\ResponseInterface[] $responses
   *   The expected responses.
   *
   * @return \GuzzleHttp\Client
   *   The client.
   */
  protected function setupMockHttpClient(array $responses) : Client {
    $client = $this->createMockHttpClient($responses);

    $this->container->set('http_client_factory', new class ($client) extends ClientFactory {

      /**
       * Constructs a new instance.
       *
       * @param \GuzzleHttp\Client $client
       *   The http client.
       */
      public function __construct(private readonly Client $client) {
      }

      /**
       * {@inheritdoc}
       */
      public function fromOptions(array $config = []) : Client {
        return $this->client;
      }

    });
    return $client;
  }

  /**
   * Process a request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  protected function processRequest(Request $request): Response {
    return $this->container->get('http_kernel')->handle($request);
  }

  /**
   * Creates a request object.
   *
   * @param string $uri
   *   The uri.
   * @param string $method
   *   The method.
   * @param array $parameters
   *   The parameters.
   * @param array $document
   *   The document.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The request.
   */
  protected function getMockedRequest(
    string $uri,
    string $method = 'GET',
    array $parameters = [],
    array $document = []
  ): Request {
    $document = $document ? Json::encode($document) : NULL;

    $request = Request::create($uri, $method, $parameters, [], [], [], $document);
    if ($document !== []) {
      $request->headers->set('Content-Type', 'application/json');
    }
    $request->headers->set('Accept', 'application/json');
    return $request;
  }

  /**
   * Creates HTTP history middleware client stub.
   *
   * @param array $container
   *   The container.
   * @param \Psr\Http\Message\ResponseInterface[]|\GuzzleHttp\Exception\GuzzleException[] $responses
   *   The expected responses.
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

<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Component\Serialization\Json;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API test base.
 */
abstract class ApiKernelTestBase extends EntityKernelTestBase implements ServiceModifierInterface {

  use ApiTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->installConfig(['helfi_api_base']);
  }

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
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

    $request = Request::create($uri, $method, $parameters, [], [], [], $document ? Json::encode($document) : NULL);
    if ($document !== []) {
      $request->headers->set('Content-Type', 'application/json');
    }
    $request->headers->set('Accept', 'application/json');
    return $request;
  }

}

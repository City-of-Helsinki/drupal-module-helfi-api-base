<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\helfi_api_base\Package\HelfiPackage;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Tests the entity_changed plugin.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\rest\resource\PackageVersion
 * @group helfi_api_base
 */
class PackageVersionTest extends ApiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'serialization',
    'rest',
  ];

  /**
   * Creates a user with correct permissions.
   */
  private function setupUser() : void {
    $user = $this->createUser(permissions: ['restful get helfi_debug_package_version']);
    $this->drupalSetCurrentUser($user);
  }

  /**
   * Tests request without permission.
   */
  public function testAccessDenied() : void {
    $this->drupalSetUpCurrentUser();

    $request = $this->getMockedRequest('/api/v1/package');
    $response = $this->processRequest($request);

    $this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $response->getStatusCode());
  }

  /**
   * Tests required query parameters.
   *
   * @dataProvider getDataRequiredParameters
   */
  public function testGetRequiredParameters(array $parameters, string $expectedErrorMessage) : void {
    $this->setupUser();
    $request = $this->getMockedRequest('/api/v1/package', parameters: $parameters);
    $response = $this->processRequest($request);

    $this->assertEquals(HttpResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertEquals($expectedErrorMessage, json_decode($response->getContent(), TRUE)['message']);
  }

  /**
   * A data provider.
   *
   * @return array[]
   *   The data.
   */
  public function getDataRequiredParameters() : array {
    return [
      [
        [], 'Missing required query argument: name',
      ],
      [
        ['name' => 'drupal/helfi_api_base'], 'Missing required query argument: version',
      ],
    ];
  }

  /**
   * Tests invalid package name.
   */
  public function testInvalidPackageName() : void {
    $this->setupUser();
    $request = $this->getMockedRequest('/api/v1/package', parameters: [
      'name' => 'drupal/invalid',
      'version' => '1.0.0',
    ]);
    $response = $this->processRequest($request);

    $this->assertEquals(HttpResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertEquals('Invalid package name: drupal/invalid', json_decode($response->getContent(), TRUE)['message']);
  }

  /**
   * Tests requests that lead to InvalidPackageException error.
   */
  public function testInvalidPackageException() : void {
    \Drupal::service('kernel')->rebuildContainer();

    $client = $this->createMockHttpClient([
      new RequestException('message', new Request('GET', 'test')),
      new RequestException('message', new Request('GET', 'test')),
    ]);
    $this->container->set('helfi_api_base.helfi_package_version_checker', new HelfiPackage($client));

    $request = $this->getMockedRequest('/api/v1/package', parameters: [
      'name' => 'drupal/helfi_api_base',
      'version' => '1.0.0',
    ]);

    $this->setupUser();
    $response = $this->processRequest($request);

    $this->assertEquals(HttpResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertEquals('No version data found.', json_decode($response->getContent(), TRUE)['message']);
  }

  /**
   * Tests get request and cache contexts.
   */
  public function testGet() : void {
    \Drupal::service('kernel')->rebuildContainer();
    $client = $this->createMockHttpClient([
      new Response(body: json_encode([
        'packages' => [
          'drupal/helfi_api_base' => [
            [
              'version' => '1.2.0',
            ],
          ],
        ],
      ])),
    ]);
    $this->container->set('helfi_api_base.helfi_package_version_checker', new HelfiPackage($client));

    $request = $this->getMockedRequest('/api/v1/package', parameters: [
      'name' => 'drupal/helfi_api_base',
      'version' => '1.0.0',
    ]);
    $this->setupUser();
    $response = $this->processRequest($request);

    $this->assertInstanceOf(CacheableResponse::class, $response);
    // Make sure our response is not sent to actual API.
    $this->assertEquals('1.2.0', json_decode($response->getContent(), TRUE)['latestVersion']);
    $this->assertContains('url.query_args:name', $response->getCacheableMetadata()->getCacheContexts());
    $this->assertContains('url.query_args:version', $response->getCacheableMetadata()->getCacheContexts());
    $this->assertEquals(180, $response->getCacheableMetadata()->getCacheMaxAge());
  }

}

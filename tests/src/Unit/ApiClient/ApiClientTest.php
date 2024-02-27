<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryBackend;
use Drupal\Core\File\Exception\FileNotExistsException;
use Drupal\helfi_api_base\ApiClient\ApiClient;
use Drupal\helfi_api_base\ApiClient\ApiResponse;
use Drupal\helfi_api_base\ApiClient\CacheValue;
use Drupal\helfi_api_base\Environment\EnvironmentResolver;
use Drupal\helfi_api_base\Environment\EnvironmentResolverInterface;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\ApiClient\ApiClient
 * @group helfi_api_base
 */
class ApiClientTest extends UnitTestCase {

  use ApiTestTrait;
  use ProphecyTrait;

  /**
   * The cache.
   *
   * @var null|\Drupal\Core\Cache\CacheBackendInterface
   */
  private ?CacheBackendInterface $cache;

  /**
   * The default environment resolver config.
   *
   * @var array
   */
  private array $environmentResolverConfiguration = [];

  /**
   * Response fixture JSON file.
   *
   * @var string
   */
  private string $fixture;

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->fixture = sprintf('%s/../../../fixtures/response.json', __DIR__);
    $this->cache = new MemoryBackend();
    $this->environmentResolverConfiguration = [
      EnvironmentResolver::PROJECT_NAME_KEY => Project::ASUMINEN,
      EnvironmentResolver::ENVIRONMENT_NAME_KEY => 'local',
    ];
  }

  /**
   * Create a new time mock object.
   *
   * @param int $expectedTime
   *   The expected time.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The mock.
   */
  private function getTimeMock(int $expectedTime) : ObjectProphecy {
    $time = $this->prophesize(TimeInterface::class);
    $time->getRequestTime()->willReturn($expectedTime);
    return $time;
  }

  /**
   * Constructs a new api client instance.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The http client.
   * @param \Drupal\Component\Datetime\TimeInterface|null $time
   *   The time prophecy.
   * @param \Psr\Log\LoggerInterface|null $logger
   *   The logger.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentResolverInterface|null $environmentResolver
   *   The environment resolver.
   * @param array $requestOptions
   *   The default request options.
   *
   * @return \Drupal\helfi_api_base\ApiClient\ApiClient
   *   The api client instance.
   */
  private function getSut(
    ClientInterface $client = NULL,
    TimeInterface $time = NULL,
    LoggerInterface $logger = NULL,
    EnvironmentResolverInterface $environmentResolver = NULL,
    array $requestOptions = [],
  ) : ApiClient {
    if (!$client) {
      $client = $this->createMockHttpClient([]);
    }

    if (!$time) {
      $time = $this->getTimeMock(time())->reveal();
    }

    if (!$logger) {
      $logger = $this->prophesize(LoggerInterface::class)->reveal();
    }
    if (!$environmentResolver) {
      $environmentResolver = new EnvironmentResolver('', $this->getConfigFactoryStub([
        'helfi_api_base.environment_resolver.settings' => $this->environmentResolverConfiguration,
      ]));
    }

    return new ApiClient(
      $client,
      $this->cache,
      $time,
      $environmentResolver,
      $logger,
      $requestOptions,
    );
  }

  /**
   * Test makeRequest().
   *
   * @covers ::__construct
   * @covers ::makeRequest
   * @covers ::getRequestOptions
   * @covers \Drupal\helfi_api_base\ApiClient\ApiResponse::__construct
   */
  public function testMakeRequest() {
    $requests = [];
    $client = $this->createMockHistoryMiddlewareHttpClient($requests, [
      new Response(200, body: json_encode([])),
      new Response(200, body: json_encode(['key' => 'value'])),
    ]);
    $sut = $this->getSut($client);

    // Test empty and non-empty response.
    for ($i = 0; $i < 2; $i++) {
      $response = $sut->makeRequest('GET', '/foo');
      $this->assertInstanceOf(ApiResponse::class, $response);
      $this->assertInstanceOf(RequestInterface::class, $requests[0]['request']);
    }
  }

  /**
   * Tests exception when cache callback fails.
   *
   * @covers ::__construct
   * @covers ::cache
   */
  public function testCacheExceptionOnFailure() : void {
    $this->expectException(GuzzleException::class);

    $this->getSut()->cache(
      'nonexistent:fi',
      fn () => throw new RequestException(
        'Test',
        $this->prophesize(RequestInterface::class)->reveal()
      )
    );
  }

  /**
   * Tests that stale cache will be returned in case callback fails.
   *
   * @covers ::__construct
   * @covers ::cache
   * @covers \Drupal\helfi_api_base\ApiClient\CacheValue::hasExpired
   * @covers \Drupal\helfi_api_base\ApiClient\CacheValue::__construct
   */
  public function testStaleCacheOnFailure() : void {
    $time = time();
    // Expired cache object.
    $cacheValue = new CacheValue(
      new ApiResponse((object) ['value' => 1]),
      $time - 10,
      [],
    );
    $this->cache->set('external_menu:main:fi', $cacheValue);

    $sut = $this->getSut(
      time: $this->getTimeMock($time)->reveal(),
    );
    $response = $sut->cache(
      'external_menu:main:fi',
      fn () => throw new RequestException(
        'Test',
        $this->prophesize(RequestInterface::class)->reveal()
      )
    );
    $this->assertInstanceOf(CacheValue::class, $response);
  }

  /**
   * Tests that stale cache can be updated.
   *
   * @covers ::__construct
   * @covers ::cache
   * @covers ::cacheMaxAge
   * @covers \Drupal\helfi_api_base\ApiClient\CacheValue::hasExpired
   * @covers \Drupal\helfi_api_base\ApiClient\CacheValue::__construct
   * @covers \Drupal\helfi_api_base\ApiClient\ApiResponse::__construct
   */
  public function testStaleCacheUpdate() : void {
    $time = time();

    // Expired cache object.
    $cacheValue = new CacheValue(
      new ApiResponse((object) ['value' => 1]),
      $time - 10,
      [],
    );
    // Populate cache with expired cache value object.
    $this->cache->set('external_menu:main:en', $cacheValue);

    $sut = $this->getSut(
      time: $this->getTimeMock($time)->reveal(),
    );
    $value = $sut->cache('external_menu:main:en', static fn () => new CacheValue(
      new ApiResponse((object) ['value' => 'value']),
      $sut->cacheMaxAge(10),
      [],
    ));
    $this->assertInstanceOf(CacheValue::class, $value);
    $this->assertInstanceOf(ApiResponse::class, $value->response);
    // Make sure cache was updated.
    $this->assertInstanceOf(\stdClass::class, $value->response->data);
    $this->assertEquals($time + 10, $value->expires);
    $this->assertEquals('value', $value->response->data->value);
    // Re-fetch the data to make sure we still get updated data and make sure
    // no further requests are made.
    $value = $sut->cache('external_menu:main:en', fn() => $this->fail('Data should be cached'));
    $this->assertInstanceOf(\stdClass::class, $value->response->data);
    $this->assertEquals('value', $value->response->data->value);
  }

  /**
   * Make sure we log the exception and then re-throw the same exception.
   *
   * @covers ::makeRequest
   * @covers ::__construct
   * @covers ::getRequestOptions
   */
  public function testRequestLoggingException() : void {
    $this->expectException(GuzzleException::class);

    $client = $this->createMockHttpClient([
      new RequestException('Test', $this->prophesize(RequestInterface::class)->reveal()),
    ]);
    $logger = $this->prophesize(LoggerInterface::class);

    $sut = $this->getSut($client, logger: $logger->reveal());
    $sut->makeRequest('GET', '/foo');
  }

  /**
   * Tests that file not found exception is thrown when no mock file exists.
   *
   * @covers ::makeRequestWithFixture
   * @covers ::__construct
   * @covers ::getRequestOptions
   * @covers \Drupal\helfi_api_base\ApiClient\ApiFixture::requestFromFile
   */
  public function testMockFallbackException() : void {
    $this->expectException(FileNotExistsException::class);
    $response = $this->prophesize(ResponseInterface::class);
    $response->getStatusCode()->willReturn(403);
    $client = $this->createMockHttpClient([
      new ClientException(
        'Test',
        $this->prophesize(RequestInterface::class)->reveal(),
        $response->reveal(),
      ),
    ]);
    $sut = $this->getSut($client);
    // Test with non-existent menu to make sure no mock file exist.
    $sut->makeRequestWithFixture(
      sprintf('%d/should-not-exists.txt', __DIR__),
      'GET',
      '/foo'
    );
  }

  /**
   * Tests that mock is used on local environment when GET request fails.
   *
   * @covers ::makeRequestWithFixture
   * @covers ::__construct
   * @covers ::getRequestOptions
   * @covers \Drupal\helfi_api_base\ApiClient\ApiResponse::__construct
   * @covers \Drupal\helfi_api_base\ApiClient\ApiFixture::requestFromFile
   */
  public function testMockFallback() : void {
    // Use logger to verify that mock file is actually used.
    $logger = $this->prophesize(LoggerInterface::class);
    $logger->warning(Argument::containingString('Mock data is used instead.'))
      ->shouldBeCalled();
    $client = $this->createMockHttpClient([
      new ConnectException(
        'Test',
        $this->prophesize(RequestInterface::class)->reveal(),
      ),
    ]);
    $sut = $this->getSut(
      $client,
      logger: $logger->reveal(),
    );
    $response = $sut->makeRequestWithFixture(
      $this->fixture,
      'GET',
      '/foo',
    );
    $this->assertInstanceOf(ApiResponse::class, $response);
  }

  /**
   * Make sure subsequent requests are failed after one failed request.
   *
   * @covers ::makeRequestWithFixture
   * @covers ::__construct
   * @covers ::getRequestOptions
   */
  public function testFastRequestFailure() : void {
    // Override environment name so we don't fallback to mock responses.
    $this->environmentResolverConfiguration[EnvironmentResolver::ENVIRONMENT_NAME_KEY] = 'test';

    $client = $this->createMockHttpClient([
      new ConnectException(
        'Test',
        $this->prophesize(RequestInterface::class)->reveal(),
      ),
    ]);
    $sut = $this->getSut($client);

    $attempts = 0;
    // Make sure only one request is sent if the first request fails.
    // This should fail to \OutOfBoundsException from guzzle MockHandler
    // if more than one request is sent.
    for ($i = 0; $i < 50; $i++) {
      try {
        $sut->makeRequestWithFixture($this->fixture, 'GET', '/foo');
      }
      catch (ConnectException) {
        $attempts++;
      }
    }
    $this->assertEquals(50, $attempts);
  }

  /**
   * Make sure cache can be bypassed when configured so.
   *
   * @covers ::makeRequest
   * @covers ::__construct
   * @covers ::cache
   * @covers ::getRequestOptions
   * @covers ::withBypassCache
   * @covers \Drupal\helfi_api_base\ApiClient\CacheValue::hasExpired
   * @covers \Drupal\helfi_api_base\ApiClient\CacheValue::__construct
   * @covers \Drupal\helfi_api_base\ApiClient\ApiResponse::__construct
   */
  public function testCacheBypass() : void {
    $time = time();
    $requests = [];
    $client = $this->createMockHistoryMiddlewareHttpClient($requests, [
      new Response(200, body: json_encode(['value' => 1])),
      new Response(200, body: json_encode(['value' => 2])),
    ]);
    $sut = $this->getSut(
      $client,
      time: $this->getTimeMock($time)->reveal()
    );
    // Make sure cache is used for all requests.
    for ($i = 0; $i < 3; $i++) {
      $response = $sut->cache('cache_key', fn () => new CacheValue(
        $sut->makeRequest('GET', '/foo'),
        $time + 100,
        [],
      ))->response;
      $this->assertInstanceOf(\stdClass::class, $response->data);
      $this->assertEquals(1, $response->data->value);
    }
    // Make sure cache is bypassed when configured so and the cached content
    // is updated.
    $response = $sut->withBypassCache()->cache('cache_key', fn () => new CacheValue(
      $sut->makeRequest('GET', '/foo'),
      $time + 100,
      []
    ))->response;
    $this->assertInstanceOf(\stdClass::class, $response->data);
    $this->assertEquals(2, $response->data->value);

    // withBypassCache() method creates a clone of ApiManager instance to ensure
    // cache is only bypassed when explicitly told so.
    // We defined only two responses, so this should fail to OutOfBoundException
    // if cache was bypassed here.
    for ($i = 0; $i < 3; $i++) {
      $response = $sut->cache('cache_key', fn () => new CacheValue(
        $sut->makeRequest('GET', '/foo'),
        $time + 100,
        [],
      ))->response;
      $this->assertInstanceOf(\stdClass::class, $response->data);
      $this->assertEquals(2, $response->data->value);
    }
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\Render\HtmlResponse;
use Drupal\helfi_api_base\Http\Response\CacheControlSubscriber;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests cache control headers.
 */
#[Group('helfi_api_base')]
#[RunTestsInSeparateProcesses]
class CacheControlSubscriberTest extends KernelTestBase {

  use ApiTestTrait;
  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'user',
    'system',
  ];

  /**
   * {@inheritDoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installConfig(['system', 'user']);
  }

  /**
   * Gets the mock response event.
   *
   * @return \Symfony\Component\HttpKernel\Event\ResponseEvent
   *   The response event.
   */
  private function getResponseEvent(HtmlResponse $response) : ResponseEvent {
    $request = Request::createFromGlobals();
    return new ResponseEvent(
      $this->container->get('http_kernel'),
      $request,
      HttpKernelInterface::MAIN_REQUEST,
      $response,
    );
  }

  /**
   * Gets the SUT.
   *
   * @return \Drupal\helfi_api_base\Http\Response\CacheControlSubscriber
   *   The sut.
   */
  private function getSut(): CacheControlSubscriber {
    return $this->container->get(CacheControlSubscriber::class);
  }

  /**
   * Tests that nothing is done if page cache is not enabled.
   */
  public function testNoPageCache(): void {
    // Test when request is not cacheable.
    $htmlResponse = new HtmlResponse();
    $event = $this->getResponseEvent($htmlResponse);
    $this->getSut()->onKernelResponse($event);

    $response = $event->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals(0, $response->getMaxAge());
    $this->assertEquals(0, $response->headers->getCacheControlDirective('s-maxage'));

    // Test cacheable response, but no max-age setting.
    $htmlResponse = new HtmlResponse();
    $htmlResponse->setPublic()
      ->setExpires(new \DateTime('+1 hour'));

    $response = $event->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals(0, $response->headers->getCacheControlDirective('max-age'));
    $this->assertEquals(0, $response->headers->getCacheControlDirective('s-maxage'));
  }

  /**
   * Tests that max-age is replaced with s-maxage.
   */
  public function testCacheControlSmaxAge(): void {
    $this->config('system.performance')
      ->set('cache.page.max_age', 3600)
      ->save();

    $htmlResponse = new HtmlResponse();
    $htmlResponse->setPublic()
      ->setMaxAge(3600)
      ->setExpires(new \DateTime('+1 hour'));

    $event = $this->getResponseEvent($htmlResponse);
    $this->getSut()->onKernelResponse($event);

    $response = $event->getResponse();

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals(0, $response->headers->getCacheControlDirective('max-age'));
    $this->assertTrue($response->headers->hasCacheControlDirective('must-revalidate'));
    $this->assertEquals(3600, $response->headers->getCacheControlDirective('s-maxage'));
  }

}

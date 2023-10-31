<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\Render\HtmlResponse;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\helfi_api_base\EventSubscriber\EnvironmentResponseSubscriber;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests environment response headers.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\EventSubscriber\EnvironmentResponseSubscriber
 * @group helfi_api_base
 */
class EnvironmentResponseSubscriberTest extends KernelTestBase {

  use EnvironmentResolverTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['helfi_api_base'];

  /**
   * Gets the response subscriber service.
   *
   * @return \Drupal\helfi_api_base\EventSubscriber\EnvironmentResponseSubscriber
   *   The response subscriber.
   */
  private function getSut() : EnvironmentResponseSubscriber {
    return $this->container->get('helfi_api_base.environment_response_subscriber');
  }

  /**
   * Gets the mock response event.
   *
   * @return \Symfony\Component\HttpKernel\Event\ResponseEvent
   *   The response event.
   */
  private function getResponseEvent(Request $request = NULL) : ResponseEvent {
    if (!$request) {
      $request = Request::createFromGlobals();
    }
    return new ResponseEvent(
      $this->container->get('http_kernel'),
      $request,
      // @todo Rename this once Core requires 7.0 symfony.
      // @phpstan-ignore-next-line
      HttpKernelInterface::MASTER_REQUEST,
      new HtmlResponse()
    );
  }

  /**
   * Asserts that response has no header when project is not defined.
   */
  public function testNoResponseHeader() : void {
    $event = $this->getResponseEvent();
    $this->getSut()->onResponse($event);
    $this->assertNotContains(EnvironmentResponseSubscriber::ENVIRONMENT_HEADER_NAME, $event->getResponse()->headers);
    $this->assertNotContains(EnvironmentResponseSubscriber::INSTANCE_HEADER_NAME, $event->getResponse()->headers);
  }

  /**
   * Asserts that response headers are set when project name is defined.
   */
  public function testHeadersExist() : void {
    $this->setActiveProject(Project::LIIKENNE, EnvironmentEnum::Test);

    $event = $this->getResponseEvent();
    $this->getSut()->onResponse($event);
    $this->assertEquals('test', $event->getResponse()->headers->get(EnvironmentResponseSubscriber::ENVIRONMENT_HEADER_NAME));
    $this->assertEquals('liikenne', $event->getResponse()->headers->get(EnvironmentResponseSubscriber::INSTANCE_HEADER_NAME));
  }

}

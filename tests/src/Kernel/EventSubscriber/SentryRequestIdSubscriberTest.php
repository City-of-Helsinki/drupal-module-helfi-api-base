<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\EventSubscriber;

use Drupal\helfi_api_base\EventSubscriber\SentryRequestIdSubscriber;
use Drupal\Tests\helfi_api_base\Kernel\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Sentry\Event;
use Sentry\State\Scope;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Tests Sentry request id event subscriber.
 */
#[RunTestsInSeparateProcesses]
#[Group('helfi_api_base')]
class SentryRequestIdSubscriberTest extends KernelTestBase {

  use ApiTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'raven',
  ];

  /**
   * Sentry tags captured during the last request.
   *
   * @var array<string, string>
   */
  private array $tags = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    // Raven ends the Sentry context when the request terminates, so the tags
    // are captured while the response is being built.
    $this->container->get(EventDispatcherInterface::class)->addListener(KernelEvents::RESPONSE, function () : void {
      \Sentry\configureScope(function (Scope $scope) : void {
        $this->tags = $scope->applyToEvent(Event::createEvent())?->getTags() ?? [];
      });
    });
  }

  /**
   * Tests that request id is added to Sentry tags.
   */
  public function testRequestId() : void {
    $request = $this->getMockedRequest('/');
    $request->headers->set('X-Request-ID', 'foo');
    $this->processRequest($request);

    $this->assertSame('foo', $this->tags['request_id'] ?? NULL);
  }

  /**
   * Tests that the subscriber runs after raven starts a new Sentry scope.
   */
  public function testPriority() : void {
    $dispatcher = $this->container->get(EventDispatcherInterface::class);

    $ravenPriority = $dispatcher->getListenerPriority(KernelEvents::REQUEST, [
      $this->container->get('raven.request_subscriber'),
      'onRequest',
    ]);
    $priority = $dispatcher->getListenerPriority(KernelEvents::REQUEST, [
      $this->container->get(SentryRequestIdSubscriber::class),
      'onRequest',
    ]);

    $this->assertLessThan($ravenPriority, $priority);
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Sentry\State\Scope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds X-Request-ID header value to Sentry events.
 */
final class SentryRequestIdSubscriber implements EventSubscriberInterface {

  /**
   * Tags the Sentry scope with the request id.
   */
  public function onRequest(RequestEvent $event) : void {
    if (!$event->isMainRequest()) {
      return;
    }

    if ($requestId = $event->getRequest()->headers->get('X-Request-ID')) {
      \Sentry\configureScope(static fn (Scope $scope) => $scope->setTag('request_id', $requestId));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      // Run after raven's RequestSubscriber, which starts a new Sentry scope.
      KernelEvents::REQUEST => ['onRequest', 221],
    ];
  }

}

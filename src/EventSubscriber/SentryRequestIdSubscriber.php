<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use LoggerExtra\LoggerContext;
use Sentry\State\Scope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds the logger context request id to Sentry events.
 */
final class SentryRequestIdSubscriber implements EventSubscriberInterface {

  /**
   * Tags the Sentry scope with the request id.
   */
  public function onRequest(RequestEvent $event) : void {
    if (!$event->isMainRequest()) {
      return;
    }

    if ($requestId = LoggerContext::get()['request_id'] ?? NULL) {
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

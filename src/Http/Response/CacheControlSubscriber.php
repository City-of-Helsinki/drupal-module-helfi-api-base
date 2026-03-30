<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Http\Response;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Disables 'max-age' header, sets 's-maxage' and 'must-revalidate' headers.
 *
 * This should allow Varnish to cache pages up to one day, but browser
 * must always revalidate the content, instead of loading it from the disk when
 * Varnish sends a 304 response.
 */
final class CacheControlSubscriber implements EventSubscriberInterface {

  /**
   * Responds to kernel response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    $response = $event->getResponse();

    if (!$response->isCacheable()) {
      return;
    }

    if (!$response->headers->hasCacheControlDirective('s-maxage')) {
      // Disable max-age and set s-maxage=86400 (one day) instead.
      $response->setSharedMaxAge(86400)
        ->setMaxAge(0);
    }

    if ($response->getStatusCode() < 400) {
      $response->headers->addCacheControlDirective('must-revalidate', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse', -10],
    ];
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Http\Response;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
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
final readonly class CacheControlSubscriber implements EventSubscriberInterface {

  public function __construct(
    #[AutowireServiceClosure(service: ConfigFactoryInterface::class)] private \Closure $configFactoryClosure,
  ) {
  }

  /**
   * Gets the config factory from service closure.
   *
   * Injecting Config factory directly into middleware causes a significant
   * performance hit.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory.
   */
  private function getConfigFactory(): ConfigFactoryInterface {
    return ($this->configFactoryClosure)();
  }

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
    $maxAge = (int) $this->getConfigFactory()
      ->get('system.performance')
      ->get('cache.page.max_age');

    if (!$maxAge) {
      return;
    }
    // Swap 'max-age' with 's-maxage' if it matches the configured
    // default.
    // This allows us to define 'max-age: 0' with 'must-revalidate' cache
    // control header, but store the response in Varnish cache using the
    // 's-maxage' header, while letting us override the 'max-age' header in
    // specific use cases, like Cookie banner controller response.
    if ($maxAge > 0 && $maxAge === $response->getMaxAge()) {
      $response->setSharedMaxAge($maxAge)
        ->setMaxAge(0);

      if ($response->getStatusCode() < 400) {
        $response->headers->addCacheControlDirective('must-revalidate', TRUE);
      }
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

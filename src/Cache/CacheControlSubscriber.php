<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Cache;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;
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
   * Responds to kernel response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    $response = $event->getResponse();

    if (!$response instanceof CacheableResponseInterface) {
      return;
    }

    if (!$response->headers->hasCacheControlDirective('max-age')) {
      return;
    }

    $maxAge = (int) $response->getCacheableMetadata()->getCacheMaxAge();

    // We treat permanent cache max-age as default therefore we don't override
    // the max-age.
    if ($maxAge === CacheBackendInterface::CACHE_PERMANENT) {
      $maxAge = (int) ($this->configFactoryClosure)()
        ->get('system.performance')
        ->get('cache.page.max_age');
    }

    // Allow 4xx pages to be cached.
    $cacheTtl4xx = Settings::get('cache_ttl_4xx', 3600);

    if ($cacheTtl4xx > 0 && $response->isClientError()) {
      $maxAge = (int) $cacheTtl4xx;
    }
    // Swap 'max-age' with 's-maxage' if it matches the configured
    // default.
    // This allows us to define 'max-age: 0' with 'must-revalidate' cache
    // control header, but store the response in Varnish cache using the
    // 's-maxage' header, while letting us override the 'max-age' header in
    // specific use cases, like Cookie banner controller response.
    $response->setSharedMaxAge($maxAge)
      ->setPublic()
      ->setMaxAge(0);

    if ($response->getStatusCode() < 400) {
      $response->headers->addCacheControlDirective('must-revalidate', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse'],
    ];
  }

}

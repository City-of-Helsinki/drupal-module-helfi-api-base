<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\helfi_api_base\Environment\EnvironmentResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds current instance and environment name to response headers.
 */
final class EnvironmentResponseSubscriber implements EventSubscriberInterface {

  public const INSTANCE_HEADER_NAME = 'X-Drupal-Instance';
  public const ENVIRONMENT_HEADER_NAME = 'X-Drupal-Environment';

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Environment\EnvironmentResolver $environmentResolver
   *   The environment resolver.
   */
  public function __construct(
    private EnvironmentResolver $environmentResolver
  ) {
  }

  /**
   * Responds to kernel response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to respond to.
   */
  public function onResponse(ResponseEvent $event) : void {
    $response = $event->getResponse();

    try {
      $environment = $this->environmentResolver
        ->getActiveEnvironment();
      $response->headers->add([self::INSTANCE_HEADER_NAME => $environment->getId()]);
      $response->headers->add([self::ENVIRONMENT_HEADER_NAME => $environment->getEnvironmentName()]);
    }
    catch (\InvalidArgumentException) {
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    $events[KernelEvents::RESPONSE][] = ['onResponse'];

    return $events;
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\helfi_api_base\Azure\PubSub\PubSubMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A cache invalidator subscriber.
 */
final class CacheTagInvalidatorSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache tag invalidator subscriber.
   */
  public function __construct(
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
  ) {
  }

  /**
   * Responds to PubSub message events.
   *
   * @param \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage $message
   *   The event to respond to.
   */
  public function onReceive(PubSubMessage $message) : void {
    if (!isset($message->data['tags'])) {
      return;
    }
    $this->cacheTagsInvalidator->invalidateTags($message->data['tags']);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      PubSubMessage::class => ['onReceive'],
    ];
  }

}

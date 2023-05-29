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
    if (!isset($message->data['data']['tags'])) {
      return;
    }
    $this->cacheTagsInvalidator->invalidateTags($message->data['data']['tags']);
    // Cache invalidator service keeps already invalidated cache tags in a
    // static cache and prevents the invalidation of the same tag multiple
    // times.
    // We run this service via Drush command, meaning the same cache tag is
    // never invalidated unless we manually reset the checksums.
    // ::resetCheckSums() seems to be an internal function meant to only be
    // used in tests.
    // Throw an exception in case someone overrides the default
    // 'cache_tags.invalidator' service or the method is removed in the future.
    // @see \Drupal\Core\Çache\CacheTagsInvalidator::resetChecksums().
    // @see \Drupal\Core\Çache\CacheTagsCheckSumTrait::invalidateTags().
    if (!method_exists($this->cacheTagsInvalidator, 'resetChecksums')) {
      throw new \LogicException('CacheTagsInvalidatorInterface::resetCheckSums() does not exist anymore.');
    }
    $this->cacheTagsInvalidator->resetCheckSums();
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

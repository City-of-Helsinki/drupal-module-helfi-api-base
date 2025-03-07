<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\helfi_api_base\Azure\PubSub\PubSubMessage;
use Drupal\helfi_api_base\Environment\EnvironmentResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
   * @param \Drupal\helfi_api_base\Environment\EnvironmentResolverInterface $environmentResolver
   *   The environment resolver.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    private readonly EnvironmentResolverInterface $environmentResolver,
    #[Autowire('@event_dispatcher')] private readonly EventDispatcherInterface $eventDispatcher,
  ) {
  }

  /**
   * Checks if the given instance is valid.
   *
   * @param array $instances
   *   The instances.
   *
   * @return bool
   *   TRUE if valid instance.
   */
  private function isValidInstance(array $instances = []) : bool {
    if (!$instances) {
      return TRUE;
    }

    try {
      $project = $this->environmentResolver->getActiveProject();

      return in_array($project->getName(), $instances);
    }
    catch (\InvalidArgumentException) {
    }
    return TRUE;
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
    $instances = $message->data['data']['instances'] ?? [];

    if (!$this->isValidInstance($instances)) {
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
    $this->cacheTagsInvalidator->resetChecksums();

    // The Purge Queue service temporarily stores all incoming revalidations
    // in a buffer (refer to: TxBufferInterface) before inserting them into the
    // queue's storage/database.
    // This method executed within a long-running loop inside a drush
    // command that also contains a blocking function.
    // In certain cases, the buffer is processed only after the loop has ended,
    // and in some failure scenarios, the buffer is not processed at all
    // resulting in tags not being purged from varnish.
    // To ensure the tag revalidations are committed to the queue's database,
    // the commit method must be invoked to process and finalize the buffer.
    // @see \Drupal\purge\Plugin\Purge\Queue\QueueService::commit().
    // @see \Drupal\helfi_proxy\Event\PurgeCommitEvent
    $this->eventDispatcher->dispatch(new Event(), 'helfi_proxy.purge_queue_commit');
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

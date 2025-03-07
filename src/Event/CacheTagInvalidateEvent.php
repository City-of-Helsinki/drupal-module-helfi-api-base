<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Cache tag invalidate event.
 */
final class CacheTagInvalidateEvent extends Event {

  /**
   * The event name.
   */
  public const EVENT_NAME = 'helfi_cache_tag_invalidate';

}

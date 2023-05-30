<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

/**
 * Provides a trait to mock cache invalidator service.
 */
trait CacheTagInvalidatorTrait {

  /**
   * Mock core's CacheTagsInvalidator class.
   *
   * The CacheTagsInvalidatorInterface does not define 'resetChecksums()'
   * method and since the default CacheTagsInvalidator class is marked
   * as final we cannot mock it.
   *
   * @return \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   *   The cache invalidator class.
   */
  protected function mockCacheInvalidator() : CacheTagsInvalidatorInterface {
    return new class () implements CacheTagsInvalidatorInterface {

      /**
       * A list of invalidated tags.
       *
       * @var array
       */
      public array $tags = [];

      /**
       * The number of times resetChecksums has been called.
       *
       * @var int
       */
      public int $checkSumResets = 0;

      /**
       * {@inheritdoc}
       */
      public function invalidateTags(array $tags) : void {
        foreach ($tags as $tag) {
          $this->tags[$tag] = $tag;
        }
      }

      /**
       * Add missing resetChecksums() method.
       *
       * @see \Drupal\Core\Cache\CacheTagsInvalidator::resetChecksums()
       */
      public function resetChecksums() : void {
        $this->checkSumResets++;
      }

    };
  }

}

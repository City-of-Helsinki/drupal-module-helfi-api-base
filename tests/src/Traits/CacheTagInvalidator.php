<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

/**
 * Mock core's CacheTagsInvalidator class.
 *
 * The CacheTagsInvalidatorInterface does not define 'resetChecksums()'
 * method, and since the default CacheTagsInvalidator class is marked
 * as final, we cannot mock it.
 */
final class CacheTagInvalidator implements CacheTagsInvalidatorInterface {

  /**
   * Constructs a new instance.
   *
   * @param array $tags
   *   The invalidated cache tags.
   * @param int $checkSumResets
   *   The checksum resets.
   */
  public function __construct(public array $tags = [], public int $checkSumResets = 0) {
  }

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

}

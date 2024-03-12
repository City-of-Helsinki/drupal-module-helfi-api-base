<?php

namespace Drupal\helfi_api_base\Token;

use Drupal\Core\Entity\EntityInterface;

/**
 * Open graph image builder.
 */
interface OGImageBuilderInterface {

  /**
   * Checks whether this builder is applicable for given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity to check.
   *
   * @return bool
   *   TRUE if this instance should handle the given entity.
   */
  public function applies(EntityInterface $entity) : bool;

  /**
   * Generate image URL.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to use for generation.
   *
   * @return string|null
   *   Image url or NULL on failure.
   */
  public function buildUrl(EntityInterface $entity) : ?string;

}

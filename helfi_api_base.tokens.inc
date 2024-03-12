<?php

/**
 * @file
 * Contains token data for helfi api base.
 */

declare(strict_types=1);

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_tokens().
 *
 * @see \Drupal\helfi_api_base\Token\OGImageManager
 */
function helfi_api_base_tokens(
  $type,
  $tokens,
  array $data,
  array $options,
  BubbleableMetadata $bubbleable_metadata
) : array {
  $replacements = [];

  foreach ($tokens as $name => $original) {
    if ($name === 'shareable-image' && isset($data[$type])) {
      $entity = $data[$type];

      if ($entity instanceof EntityInterface) {
        /** @var \Drupal\helfi_api_base\Token\OGImageManager $image_manager */
        $image_manager = \Drupal::service('helfi_api_base.og_image_manager');

        $replacements[$original] = $image_manager->buildUrl($entity);
      }
    }
  }

  return $replacements;
}

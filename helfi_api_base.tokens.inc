<?php

declare(strict_types=1);

use Drupal\Core\Render\BubbleableMetadata;

/**
* Implements hook_tokens().
*/
function helfi_api_base_tokens(
  $type,
  $tokens,
  array $data,
  array $options,
  BubbleableMetadata $bubbleable_metadata
) : array {
  /** @var \Drupal\helfi_api_base\Token\TokenManager $token_manager */
  $token_manager = \Drupal::service('helfi_api_base.token_manager');

  return $token_manager
    ->getReplacements($type, $tokens, $data, $options, $bubbleable_metadata);
}

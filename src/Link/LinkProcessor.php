<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Link;

use Drupal\Core\Render\Element\Link;
use Drupal\Core\Url;
use Drupal\helfi_api_base\Helper\ExternalUri;

/**
 * Provides a whitelist functionality for all links.
 */
final class LinkProcessor extends Link {

  /**
   * {@inheritdoc}
   */
  public static function preRenderLink($element) : array {
    if (isset($element['#url']) && $element['#url'] instanceof Url) {
      $externalUrl = new ExternalUri(clone $element['#url']);

      $element['#title'] = [
        '#theme' => 'helfi_link',
        '#url' => $element['#url'],
        '#title' => $element['#title'],
      ];
      // We can't set URL's 'external' property to FALSE, because it will break
      // the URL validation.
      if ($externalUrl->isExternal()) {
        $element['#attributes']['data-is-external'] = 'true';
      }
      $element['#title']['#attributes'] = $element['#attributes'] ?? [];
    }
    return parent::preRenderLink($element);
  }

}

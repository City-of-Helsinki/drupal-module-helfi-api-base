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

      // We can't set URL's 'external' property to FALSE, because it will break
      // the URL validation.
      if ($externalUrl->isExternal()) {
        if (!is_array($element['#title'])) {
          $element['#title'] = [
            '#theme' => 'helfi_link',
            '#url' => $element['#url'],
            '#title' => $element['#title'],
          ];
        }
        $element['#attributes']['data-is-external'] = 'true';
      }
    }
    return parent::preRenderLink($element);
  }

}

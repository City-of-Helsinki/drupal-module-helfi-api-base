<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Link;

use Drupal\Core\Render\Element\Link;
use Drupal\Core\Url;

/**
 * Provides a whitelist functionality for all links.
 */
final class LinkProcessor extends Link {

  /**
   * {@inheritdoc}
   */
  public static function preRenderLink($element) : array {
    if (isset($element['#url']) && $element['#url'] instanceof Url) {
      /** @var \Drupal\helfi_api_base\Link\InternalDomainResolver $resolver */
      $resolver = \Drupal::service('helfi_api_base.internal_domain_resolver');

      $element['#title'] = [
        '#theme' => 'helfi_link',
        '#url' => $element['#url'],
        '#title' => $element['#title'],
      ];

      // We can't set URI's 'external' property to FALSE, because it will
      // break the URL validation.
      if ($resolver->isExternal($element['#url'])) {
        $element['#attributes']['data-is-external'] = 'true';

        $scheme = parse_url($element['#url']->getUri(), PHP_URL_SCHEME);

        // Skip generic schemes since we're not interested in them.
        if (!in_array($scheme, ['http', 'https'])) {
          $element['#attributes']['data-protocol'] = $scheme;
        }
      }
      $element['#title']['#attributes'] = $element['#attributes'] ?? [];
    }
    return parent::preRenderLink($element);
  }

}

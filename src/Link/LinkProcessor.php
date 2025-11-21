<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Link;

use Drupal\Core\Render\Element\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Provides a whitelist functionality for all links.
 */
final class LinkProcessor extends Link {

  /**
   * {@inheritdoc}
   */
  public static function preRenderLink($element) : array {
    $useTemplate = FALSE;

    if (isset($element['#url']) && $element['#url'] instanceof Url) {
      $url = $element['#url'];

      if ($element['#title'] instanceof Markup) {
        $useTemplate = TRUE;
      }

      /** @var \Drupal\helfi_api_base\Link\InternalDomainResolver $resolver */
      $resolver = \Drupal::service('helfi_api_base.internal_domain_resolver');

      // We can't set URI's 'external' property to FALSE, because it will
      // break the URL validation.
      if ($resolver->isExternal($url)) {
        $element['#attributes']['data-is-external'] = 'true';

        if ($scheme = $resolver->getProtocol($url)) {
          $element['#attributes']['data-protocol'] = $scheme;
        }
        $useTemplate = TRUE;
      }
    }

    if ($useTemplate) {
      $element['#title'] = [
        '#theme' => 'helfi_link',
        '#url' => $element['#url'],
        '#title' => $element['#title'],
      ];
      $element['#title']['#attributes'] = $element['#attributes'] ?? [];
    }

    // Make sure we always have a title.
    if ($element['#title'] === NULL) {
      $element['#title'] = '';
    }
    return parent::preRenderLink($element);
  }

}

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

      // In some cases we need to enrich title, so we use #theme helfi_link
      // but in cases where this is not needed, having a twig for any title
      // adds unnecessary load time, so we need to be specific when to use helfi_link.
      $use_helfi_link = FALSE;

      // We can't set URI's 'external' property to FALSE, because it will
      // break the URL validation.
      if ($resolver->isExternal($element['#url'])) {
        $element['#attributes']['data-is-external'] = 'true';

        if ($scheme = $resolver->getProtocol($element['#url'])) {
          $element['#attributes']['data-protocol'] = $scheme;
        }
      }

      if (!empty($element['#attributes']['data-is-external'])) {
        $use_helfi_link = TRUE;
      }
      if (!empty($element['#attributes']['data-protocol'])) {
        $use_helfi_link = TRUE;
      }

      /** @var \Drupal\Core\Url $url */
      $url = $element['#url'];
      $url_attributes = $url->getOption('attributes');
      if (!empty($url_attributes['data-selected-icon'])
        || !empty($element['#attributes']['data-selected-icon'])) {
        $use_helfi_link = TRUE;
      }

      if ($use_helfi_link) {
        $element['#title'] = [
          '#theme' => 'helfi_link',
          '#url' => $element['#url'],
          '#title' => $element['#title'],
        ];
        $element['#title']['#attributes'] = $element['#attributes'] ?? [];
      }
    }
    return parent::preRenderLink($element);
  }

}

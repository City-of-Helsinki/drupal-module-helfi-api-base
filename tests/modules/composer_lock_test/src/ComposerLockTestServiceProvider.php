<?php

declare(strict_types = 1);

namespace Drupal\composer_lock_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Overrides the default composer lock file location.
 */
final class ComposerLockTestServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Use fixture composer lock file.
    $path = sprintf('%s/../../../fixtures/composer.lock', __DIR__);
    $container->setParameter('helfi_api_base.default_composer_lock', $path);
  }

}

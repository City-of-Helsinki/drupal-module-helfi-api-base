<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\helfi_api_base\DebugDataItemPluginManager;

/**
 * Returns responses for Helfi Debug routes.
 */
final class DebugController extends ControllerBase {

  use AutowireTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\DebugDataItemPluginManager $manager
   *   The plugin manager.
   */
  public function __construct(private DebugDataItemPluginManager $manager) {
  }

  /**
   * Builds the response.
   *
   * @return array
   *   The response.
   */
  public function build() : array {
    $build = [];

    foreach ($this->manager->getDefinitions() as $definition) {
      $id = Html::cleanCssIdentifier($definition['id']);

      /** @var \Drupal\helfi_api_base\DebugDataItemInterface $instance */
      $instance = $this->manager
        ->createInstance($definition['id']);

      $build[$id] = [
        '#theme' => 'debug_item',
        '#id' => $definition['id'],
        '#label' => $instance->label(),
        '#data' => $instance->collect(),
      ];

      if ($instance instanceof CacheableDependencyInterface) {
        $metadata = (new CacheableMetadata())
          ->addCacheableDependency($instance);
        $metadata->applyTo($build);
      }
    }
    return $build;
  }

}

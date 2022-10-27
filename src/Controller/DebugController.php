<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\helfi_api_base\DebugDataItemPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Helfi Debug routes.
 */
final class DebugController extends ControllerBase {

  /**
   * The debug item plugin manager.
   *
   * @var \Drupal\helfi_api_base\DebugDataItemPluginManager
   */
  protected DebugDataItemPluginManager $manager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\DebugDataItemPluginManager $pluginManager
   *   The plugin manager.
   */
  public function __construct(DebugDataItemPluginManager $pluginManager) {
    $this->manager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : static {
    return new static(
      $container->get('plugin.manager.debug_data_item')
    );
  }

  /**
   * Tests invalid argument exception.
   *
   * @todo Remove this.
   */
  public function exceptionTest() : array {
    throw new \InvalidArgumentException('Test');
  }

  /**
   * Tests broken include.
   *
   * @todo Remove this.
   */
  public function includeTest() : array {
    include_once 'broken_include';
    return ['#cache' => ['max-age' => 0]];
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

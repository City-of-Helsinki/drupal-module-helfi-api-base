<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\helfi_api_base\DebugDataItemPluginManager;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents Debug records as resources.
 *
 * @RestResource (
 *   id = "helfi_debug_data",
 *   label = @Translation("Debug data"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/debug"
 *   }
 * )
 */
final class DebugDataResource extends ResourceBase implements DependentPluginInterface {

  /**
   * The debug data plugin manager.
   *
   * @var \Drupal\helfi_api_base\DebugDataItemPluginManager
   */
  private DebugDataItemPluginManager $manager;

  /**
   * The list of initialized plugins.
   *
   * @var \Drupal\helfi_api_base\DebugDataItemInterface[]
   */
  private array $debugDataPlugins = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) : self {
    $instance = parent::create(
      $container,
      $configuration,
      $plugin_id,
      $plugin_definition
    );
    $instance->manager = $container->get('plugin.manager.debug_data_item');

    return $instance;
  }

  /**
   * Initializes debug data plugins.
   *
   * @return \Drupal\helfi_api_base\DebugDataItemInterface[]
   *   The list of debug data plugins.
   */
  private function getDataPlugins() : array {
    if (!$this->debugDataPlugins) {
      foreach ($this->manager->getDefinitions() as $definition) {
        $this->debugDataPlugins[$definition['id']] = $this->manager
          ->createInstance($definition['id']);
      }
    }
    return $this->debugDataPlugins;
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get() : ResourceResponse {
    $cacheableMetadata = new CacheableMetadata();

    $data = [];
    foreach ($this->getDataPlugins() as $id => $instance) {
      $data[$id] = [
        'label' => $instance->label(),
        'data' => $instance->collect(),
      ];

      if ($instance instanceof CacheableDependencyInterface) {
        $cacheableMetadata->addCacheableDependency($instance);
      }
    }
    return (new ResourceResponse($data))
      ->addCacheableDependency($cacheableMetadata);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = [
      'module' => ['user'],
    ];

    foreach ($this->getDataPlugins() as $plugin) {
      foreach ($plugin->calculateDependencies() as $type => $value) {
        if (!isset($dependencies[$type])) {
          $dependencies[$type] = [];
        }
        // Merge existing dependencies together, make them unique and
        // reindex the dependency list.
        $dependencies[$type] = array_values(
          array_unique(
            array_merge($dependencies[$type], $value)
          )
        );
      }
    }
    return $dependencies;
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\helfi_api_base\Attribute\DebugDataItem;

/**
 * DebugDataItem plugin manager.
 */
class DebugDataItemPluginManager extends DefaultPluginManager {

  /**
   * Constructs DebugDataItemPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/DebugDataItem',
      $namespaces,
      $module_handler,
      'Drupal\helfi_api_base\DebugDataItemInterface',
      DebugDataItem::class,
    );
    $this->alterInfo('debug_data_item_info');
    $this->setCacheBackend($cache_backend, 'debug_data_item_plugins');
  }

}

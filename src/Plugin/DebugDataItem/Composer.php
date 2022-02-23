<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\DebugDataItem;

use ComposerLockParser\ComposerInfo;
use ComposerLockParser\Package;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\helfi_api_base\DebugDataItemPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the debug_data_item.
 *
 * @DebugDataItem(
 *   id = "composer",
 *   label = @Translation("Composer"),
 *   description = @Translation("Composer")
 * )
 */
class Composer extends DebugDataItemPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The composer info.
   *
   * @var \ComposerLockParser\ComposerInfo
   */
  private ComposerInfo $composerInfo;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->composerInfo = $container->get('helfi_api_base.composer_info');
    return $instance;
  }

  /**
   * Checks if package should be included in collection or not.
   *
   * @param \ComposerLockParser\Package $package
   *   The package.
   *
   * @return bool
   *   TRUE if package should be included.
   */
  private function includePackage(Package $package) : bool {
    return match(TRUE) {
      str_starts_with($package->getName(), 'drupal/helfi_'),
      str_starts_with($package->getName(), 'drupal/hdbt') => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritdoc}
   */
  public function collect(): array {
    /** @var \ComposerLockParser\Package[] $packages */
    $packages = $this->composerInfo->getPackages();

    $data = [];
    foreach ($packages as $package) {
      if (!$this->includePackage($package)) {
        continue;
      }
      $data['packages'][] = [
        'name' => $package->getName(),
        'version' => $package->getVersion(),
        'time' => $package->getTime()?->format('c'),
      ];
    }
    return $data;
  }

}

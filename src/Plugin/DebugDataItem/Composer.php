<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\DebugDataItem;

use ComposerLockParser\ComposerInfo;
use ComposerLockParser\Package;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\helfi_api_base\Attribute\DebugDataItem;
use Drupal\helfi_api_base\Debug\SupportsCollectionsInterface;
use Drupal\helfi_api_base\DebugDataItemPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the debug_data_item.
 */
#[DebugDataItem(
  id: 'composer',
  title: new TranslatableMarkup('Composer'),
)]
final class Composer extends DebugDataItemPluginBase implements ContainerFactoryPluginInterface, SupportsCollectionsInterface {

  /**
   * The composer info.
   *
   * @var \ComposerLockParser\ComposerInfo
   */
  private ComposerInfo $composerInfo;

  /**
   * The packages cache.
   *
   * @var \ComposerLockParser\Package[]
   */
  private array $packages = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) : self {
    $instance = new self($configuration, $plugin_id, $plugin_definition);
    $instance->composerInfo = $container->get('helfi_api_base.composer_info');
    return $instance;
  }

  /**
   * Checks if package should be included in collection or not.
   *
   * @param string $package
   *   The package.
   *
   * @return bool
   *   TRUE if package should be included.
   */
  private function isValidPackage(string $package) : bool {
    return match(TRUE) {
      str_starts_with($package, 'drupal/helfi_'),
      str_starts_with($package, 'drupal/hdbt') => TRUE,
      default => FALSE,
    };
  }

  /**
   * Gets the included package.
   *
   * @return \ComposerLockParser\Package[]
   *   The packages.
   */
  public function getPackages() : array {
    if (!$this->packages) {
      $this->packages = iterator_to_array($this->composerInfo->getPackages());
    }

    return array_filter($this->packages, function (Package $package) {
      return $this->isValidPackage($package->getName());
    });
  }

  /**
   * {@inheritdoc}
   */
  public function collect(): array {
    $data = [];

    foreach ($this->getPackages() as $package) {
      $data['packages'][] = [
        'name' => $package->getName(),
        'source' => $package->getSource(),
        'releases_url' => $this->getReleasesUrl($package),
        'version' => $package->getVersion(),
        'time' => $package->getTime()?->format('c'),
      ];
    }
    return $data;
  }

  /**
   * Gets the releases page URL.
   *
   * @param \ComposerLockParser\Package $package
   *   The package to parse.
   *
   * @return \Drupal\Core\Url|null
   *   The releases page URL on GitHub or null.
   */
  private function getReleasesUrl(Package $package) : ?Url {
    $source = $package->getSource();

    if (empty($source['url'])) {
      return NULL;
    }
    $path = parse_url($source['url'], PHP_URL_PATH);

    // Deal with git@github.com URLs.
    if (!$path || str_starts_with($path, 'git@')) {
      [, $path] = explode(':', $source['url'], 2);
    }
    $path = strtolower(ltrim($path, '/'));
    $path = str_replace('.git', '', $path);

    return Url::fromUri(sprintf('https://github.com/%s/releases', $path));
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\helfi_api_base\Package\Version;
use Drupal\helfi_api_base\Package\VersionChecker;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Represents Package version as resources.
 *
 * @RestResource(
 *   id = "helfi_debug_package_version",
 *   label = @Translation("Package version"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/package"
 *   }
 * )
 */
final class PackageVersion extends ResourceBase implements DependentPluginInterface {

  /**
   * The version checker service.
   *
   * @var \Drupal\helfi_api_base\Package\VersionChecker
   */
  private VersionChecker $packageVersion;

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
    $instance->packageVersion = $container->get('helfi_api_base.package_version_checker');
    return $instance;
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get(Request $request) : ResourceResponse {
    $args = [];
    foreach (['name', 'version'] as $arg) {
      if (!$value = $request->query->get($arg)) {
        throw new BadRequestHttpException(sprintf('Missing required query argument: %s', $arg));
      }
      if (is_array($value)) {
        $value = reset($value);
      }
      $args[$arg] = (string) $value;
    }
    ['name' => $name, 'version' => $version] = $args;

    $data = $this->packageVersion->get($name, $version);

    if (!$data instanceof Version) {
      throw new BadRequestHttpException(sprintf('Invalid package name: %s', $name));
    }

    $cacheableMetadata = new CacheableMetadata();
    $cacheableMetadata->addCacheContexts([
      'url.query_args:name',
      'url.query_args:version',
    ])
      ->setCacheMaxAge(180);

    return (new ResourceResponse($data->toArray()))
      ->addCacheableDependency($cacheableMetadata);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = [];
    return $dependencies;
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\DebugDataItem;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\helfi_api_base\DebugDataItemPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the debug_data_item.
 *
 * @DebugDataItem(
 *   id = "helfi_search_api_index",
 *   label = @Translation("SearchApi index"),
 *   description = @Translation("SearchApi index")
 * )
 */
class SearchApiIndex extends DebugDataItemPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    if (
      $container->has('elasticsearch_connector.cluster_manager') &&
      $container->has('elasticsearch_connector.client_manager')
    ) {
      $instance->clusterManager = $container->get('elasticsearch_connector.cluster_manager');
      $instance->clientManager = $container->get('elasticsearch_connector.client_manager');
    }
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(): array {
    $data = ['indexes' => []];

    if (
      !property_exists($this, 'clusterManager') ||
      !property_exists($this, 'clientManager')
    ) {
      return $data;
    }

    try {
      $indexes = $this->entityTypeManager
        ->getStorage('search_api_index')
        ->loadMultiple();

      $clusters = $this->clusterManager->loadAllClusters(FALSE);
      $cluster = reset($clusters);
    }
    catch (\Exception $e) {
      return $data;
    }

    if ($indexes) {
      /** @var \Drupal\search_api\IndexInterface $index */
      foreach ($indexes as $index) {
        $tracker = $index->getTrackerInstance();

        $result = $this->resolveResult(
          $tracker->getIndexedItemsCount(),
          $tracker->getTotalItemsCount()
        );

        $client = $this->clientManager->getClientForCluster($cluster);
        $cluster_status = $client->getClusterInfo()['health']['status'] ?? FALSE;

        $data['indexes'][] = [
          $index->getOriginalId() => $result,
          'cluster_status' => $cluster_status,
        ];
      }
    }

    return $data;
  }

  /**
   * Resolve return value based on index status.
   *
   * @param int $indexed
   *   Amount of up-to-date items in index.
   * @param int $total
   *   Maximum amount of items in index.
   *
   * @return string
   *   Status.
   */
  private function resolveResult(int $indexed, int $total): string {
    if ($indexed == 0 || $total == 0) {
      return 'indexing or index rebuild required';
    }

    if ($indexed === $total) {
      return 'Index up to date';
    }

    return "$indexed/$total";
  }

}

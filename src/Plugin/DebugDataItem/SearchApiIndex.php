<?php /** @noinspection PhpFieldAssignmentTypeMismatchInspection */

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\DebugDataItem;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\helfi_api_base\DebugDataItemPluginBase;
use Drupal\search_api\Tracker\TrackerInterface;
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
class SearchApiIndex extends DebugDataItemPluginBase implements ContainerFactoryPluginInterface
{

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
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(): array {
    $data = ['indexes' => []];

    // @todo: Check if search api is enabled.
    try {
      $indexes = $this->entityTypeManager
        ->getStorage('search_api_index')
        ->loadMultiple();
    }
    catch(\Exception $e) {
      return $data;
    }

    if ($indexes) {
      /** @var \Drupal\search_api\IndexInterface $index */
      foreach($indexes as $index) {
        $tracker = $index->getTrackerInstance();

        $result = $this->resolveResult(
          $tracker->getIndexedItemsCount(),
          $tracker->getTotalItemsCount()
        );

        $data['indexes'] = [$index->getServerId() => $result];
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

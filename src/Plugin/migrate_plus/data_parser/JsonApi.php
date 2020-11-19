<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\migrate_plus\data_parser;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\migrate\MigrateException;
use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;
use Webmozart\Assert\Assert;

/**
 * Obtain OpenAPI json data for migration.
 *
 * @DataParser(
 *   id = "json_api",
 *   title = @Translation("JSON:API")
 * )
 */
final class JsonApi extends Json {

  /**
   * The total count.
   *
   * @var int
   */
  protected int $count = 0;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->urls = $this->buildUrls($configuration['urls'][0]);
  }

  /**
   * Builds urls dynamically based on JSON:API meta.
   *
   * @param string $url
   *   The base url.
   *
   * @return array
   *   An array of URLs to fetch.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function buildUrls(string $url) : array {
    Assert::keyExists($this->configuration, 'meta');
    Assert::allInArray([
      'limit_key',
      'offset_key',
      'total_key',
    ], array_keys($this->configuration['meta']));

    [
      'limit_key' => $limitKey,
      'offset_key' => $offsetKey,
      'total_key' => $totalKey,
    ] = $this->configuration['meta'];

    $response = $this->getDataFetcherPlugin()->getResponseContent($url);

    // Convert objects to associative arrays.
    $source_data = json_decode($response->getContents(), TRUE);

    foreach ([$limitKey, $offsetKey, $totalKey] as $key) {
      if (!isset($source_data['meta'][$key])) {
        throw new MigrateException('Required META data not found from API.');
      }
    }
    [$limitKey => $limit, $totalKey => $total] = $source_data['meta'];
    // Update the total count.
    $this->count = $total;

    $currentUrl = UrlHelper::parse($url);

    $urls = [];

    for ($i = 1; $i <= ceil($total / $limit); $i++) {
      $currentUrl['query'][$offsetKey] = $limit * $i;

      $urls[] = Url::fromUri($currentUrl['path'], [
        'query' => $currentUrl['query'],
        'fragment' => $currentUrl['fragment'],
      ])->toString();
    }

    return $urls;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->count;
  }

}

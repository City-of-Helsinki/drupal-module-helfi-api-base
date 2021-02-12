<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\migrate\source;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\helfi_api_base\MigrateTrait;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a HTTP source plugin.
 */
abstract class HttpSourcePluginBase extends SourcePluginBase implements CacheableDependencyInterface {

  use MigrateTrait;

  /**
   * Whether to use request cache or not.
   *
   * @var bool
   */
  protected bool $useRequestCache = TRUE;

  /*
   * The number of ignored rows until we stop the migrate.
   *
   * This assumes that your API can be sorted in a way that the newest
   * changes are listed first.
   *
   * For this to have any effect 'track_changes' source setting must be set to
   * true and you must run the migrate with PARTIAL_MIGRATE=1 setting.
   *
   * @var int
   */
  protected const NUM_IGNORED_ROWS_BEFORE_STOPPING = 20;

  /**
   * The http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * An array of entity ids to import.
   *
   * @var array
   */
  protected array $entityIds = [];

  /**
   * The static cache to store data.
   *
   * @var array
   */
  protected array $data = [];

  /**
   * Keep track of ignored rows to stop migrate after N ignored rows.
   *
   * @var int
   */
  protected int $ignoredRows = 0;

  /**
   * The cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected CacheBackendInterface $dataCache;

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() : array {
    return [
      'migrate-data-' . (string) $this,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() : int {
    return CacheBackendInterface::CACHE_PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() : array {
    return [];
  }

  /**
   * Gets the cache key for given id.
   *
   * @param string $id
   *   The id.
   *
   * @return string
   *   The cache key.
   */
  protected function getCacheKey(string $id) : string {
    $id = preg_replace('/[^a-z0-9_]+/s', '_', $id);

    return sprintf('migrate-data-%s_%s', (string) $this, $id);
  }

  /**
   * Gets cached data for given id.
   *
   * @param string $id
   *   The id.
   *
   * @return array|null
   *   The cached data or null.
   */
  protected function getFromCache(string $id) : ? array {
    if (!$this->useRequestCache) {
      return NULL;
    }
    $key = $this->getCacheKey($id);

    if (isset($this->data[$key])) {
      return $this->data[$key];
    }

    if ($data = $this->dataCache->get($key)) {
      return $data->data;
    }
    return NULL;
  }

  /**
   * Sets the cache.
   *
   * @param string $id
   *   The id.
   * @param mixed $data
   *   The data.
   *
   * @return $this
   *   The self.
   */
  protected function setCache(string $id, $data) : self {
    if (!$this->useRequestCache) {
      return $this;
    }
    $key = $this->getCacheKey($id);
    $this->dataCache->set($key, $data, CacheBackendInterface::CACHE_PERMANENT, $this->getCacheTags());
    return $this;
  }

  /**
   * Sends a HTTP request and returns response data as array.
   *
   * @param string $url
   *   The url.
   *
   * @return array
   *   The JSON returned by API service.
   */
  protected function getContent(string $url) : array {
    if ($data = $this->getFromCache($url)) {
      return $data;
    }

    try {
      $content = (string) $this->httpClient->request('GET', $url)->getBody();
      $content = \GuzzleHttp\json_decode($content, TRUE);
      $this->setCache($url, $content);

      return $content;
    }
    catch (GuzzleException $e) {
    }
    return [];
  }

  /**
   * Initializes the list iterator.
   *
   * @return \Iterator
   *   The iterator.
   */
  abstract protected function initializeListIterator() : \Iterator;

  /**
   * Initializes iterator with set of entity IDs.
   *
   * @return \Iterator
   *   The iterator.
   */
  protected function initializeSingleImportIterator() : \Iterator {
    foreach ($this->entityIds as $entityId) {
      yield $this->getContent($this->buildCanonicalUrl($entityId));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    if ($this->entityIds) {
      return $this->initializeSingleImportIterator();
    }
    return $this->initializeListIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    parent::next();

    // Check if the current row has changes and increment ignoredRows variable
    // to allow us to stop migrate early if we have no changes.
    if ($this->isPartialMigrate() && $this->currentRow && !$this->currentRow->changed()) {
      $this->ignoredRows++;
    }
  }

  /**
   * Builds a canonical url to individual entity.
   *
   * @param string $id
   *   The entity ID.
   *
   * @return string
   *   The url to canonical page of given entity.
   */
  protected function buildCanonicalUrl(string $id) : string {
    $urlParts = UrlHelper::parse($this->configuration['url']);
    $query = UrlHelper::buildQuery($urlParts['query']);

    $url = vsprintf('%s/%s', [
      rtrim($urlParts['path'], '/'),
      $id,
    ]);

    if ($query) {
      $url = sprintf('%s?%s', $url, $query);
    }
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration = NULL
  ) {
    $instance = new static($configuration, $plugin_id, $plugin_definition, $migration);
    $instance->httpClient = $container->get('http_client');
    $instance->dataCache = $container->get('cache.default');

    if (!isset($configuration['url'])) {
      throw new \InvalidArgumentException('The "url" configuration missing.');
    }

    // Allow certain entity IDs to be updated.
    if (isset($migration->entity_ids)) {
      $instance->entityIds = $migration->entity_ids;
    }
    return $instance;
  }

}

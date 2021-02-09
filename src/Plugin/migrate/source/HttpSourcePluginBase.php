<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\migrate\source;

use Drupal\Component\Utility\UrlHelper;
use Drupal\helfi_api_base\MigrateTrait;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a HTTP source plugin.
 */
abstract class HttpSourcePluginBase extends SourcePluginBase {

  use MigrateTrait;

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
   * Sends a HTTP request and returns response data as array.
   *
   * @param string $url
   *   The url.
   *
   * @return array
   *   The JSON returned by API service.
   */
  protected function getContent(string $url) : array {
    try {
      $content = (string) $this->httpClient->request('GET', $url)->getBody();
      return \GuzzleHttp\json_decode($content, TRUE);
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

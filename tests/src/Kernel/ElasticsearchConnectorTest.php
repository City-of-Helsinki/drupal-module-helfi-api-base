<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\elasticsearch_connector\Plugin\search_api\backend\ElasticSearchBackend;
use Drupal\helfi_api_base\Plugin\ElasticSearch\Connector\HelfiConnector;
use Elastic\Elasticsearch\Client;

/**
 * Test for elasticsearch connector plugin.
 */
class ElasticsearchConnectorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'search_api',
    'elasticsearch_connector',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('search_api_server');
  }

  /**
   * Tests custom elasticsearch connector.
   */
  public function testSearchApiConnector() {
    // Add elasticsearch server configuration.
    $config = $this->config('search_api.server.default');
    $config->setData([
      'status' => TRUE,
      'id' => 'default',
      'name' => 'elasticsearch_server',
      'description' => 'Test server',
      'backend' => 'elasticsearch',
      'backend_config' => [
        'connector' => 'helfi_connector',
        'connector_config' => [
          'url' => 'http://elasticsearch.example.com:9200',
          'username' => '123',
          'password' => '456',
        ],
      ],
    ]);
    $config->save();

    /** @var \Drupal\search_api\ServerInterface $server */
    $server = $this->container
      ->get(EntityTypeManagerInterface::class)
      ->getStorage('search_api_server')
      ->load('default');

    $backend = $server->getBackend();
    assert($backend instanceof ElasticSearchBackend);
    $connector = $backend->getConnector();
    $this->assertInstanceOf(HelfiConnector::class, $connector);
    $this->assertInstanceOf(Client::class, $connector->getClient());
  }

}

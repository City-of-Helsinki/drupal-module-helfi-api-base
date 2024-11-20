<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\elasticsearch_connector\Plugin\search_api\backend\ElasticSearchBackend;
use Drupal\helfi_api_base\Plugin\ElasticSearch\Connector\HelfiConnector;

/**
 * Test for elasticsearch connector plugin.
 */
class ElasticsearchConnectorTest extends BrowserTestBase {

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
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Create an admin user.
    $admin_user = $this->drupalCreateUser([
      'access administration pages',
      'administer search_api',
    ]);
    $this->drupalLogin($admin_user);
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
    $this->assertInstanceOf(HelfiConnector::class, $backend->getConnector());

    $assert_session = $this->assertSession();
    $this->drupalGet(Url::fromRoute('entity.search_api_server.edit_form', [
      'search_api_server' => 'default',
    ]));
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains('Helfi Connector');
  }

}

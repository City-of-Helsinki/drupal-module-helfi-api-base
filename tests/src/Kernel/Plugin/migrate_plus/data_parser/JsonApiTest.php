<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel\Plugin\migrate_plus\data_parser;

use Drupal\helfi_api_base\Plugin\migrate_plus\data_parser\JsonApi;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate_plus\DataParserPluginManager;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use GuzzleHttp\Psr7\Response;

/**
 * Tests the entity_changed plugin.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\migrate_plus\data_parser\JsonApi
 * @group helfi_api_base
 */
class JsonApiTest extends ApiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate_plus',
    'migrate',
    'system',
    'remote_entity_test',
  ];

  /**
   * The migration plugin manager.
   *
   * @var null|\Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected ?MigrationPluginManagerInterface $migrationPluginManager;

  /**
   * The source plugin manager.
   *
   * @var null|\Drupal\migrate\Plugin\MigrateSourcePluginManager
   */
  protected ?MigrateSourcePluginManager $sourcePluginManager;

  /**
   * The data parser plugin manager.
   *
   * @var \Drupal\migrate_plus\DataParserPluginInterface|object|null
   */
  protected ?DataParserPluginManager $dataParserPluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('remote_entity_test');
    $this->installConfig(static::$modules);

    $this->sourcePluginManager = $this->container->get('plugin.manager.migrate.source');
    $this->migrationPluginManager = $this->container->get('plugin.manager.migration');
    $this->dataParserPluginManager = $this->container->get('plugin.manager.migrate_plus.data_parser');
  }

  /**
   * Tests required meta settings.
   *
   * @dataProvider missingMetaSettingsProvider
   */
  public function testMissingMetaSettings(array $configuration) : void {
    $configuration['urls'] = ['https://localhost'];
    $configuration['item_selector'] = '';

    $this->expectException(\InvalidArgumentException::class);
    JsonApi::create($this->container, $configuration, 'json_api', []);
  }

  /**
   * Data provider for testMissingMetaSettings().
   *
   * @return array
   *   An array of configurations.
   */
  public function missingMetaSettingsProvider() : array {
    return [
      [
        [],
      ],
      [
        ['meta' => []],
      ],
      [
        ['meta' => ['limit_key' => 'limit', 'offset_key' => 'offset']],
      ],
      [
        ['meta' => ['total' => 'limit', 'offset_key' => 'offset']],
      ],
    ];
  }

  /**
   * Tests that urls are added to the list.
   */
  public function testUrlResolving() : void {
    $this->container->set('http_client', $this->createMockHttpClient([
      new Response(200, [], $this->getFixture('helfi_api_base', 'jsonapi.paging.json')),
      new Response(200, [], json_encode([])),
    ]));

    /** @var \Drupal\helfi_api_base\Plugin\migrate_plus\data_parser\JsonApi $instance */
    $instance = $this->dataParserPluginManager->createInstance('json_api', [
      'data_fetcher_plugin' => 'http',
      'meta' => [
        'limit_key' => 'limit',
        'offset_key' => 'offset',
        'total_key' => 'total_count',
      ],
      'fields' => [
        ['name' => 'id', 'selector' => '/id'],
        ['name' => 'name', 'selector' => '/name'],
      ],
      'ids' => [
        'id' => ['type' => 'string'],
      ],
      'item_selector' => 'object',
      'urls' => ['https://localhost/v1/issue/?order_by=-last_modified_time'],
    ]);

    // Make sure total count is updated from 'meta' data.
    $this->assertCount(40, $instance);
  }

}

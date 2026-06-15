<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\AuditLog;

use Drupal\Core\Site\Settings;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Drupal\helfi_api_base\AuditLog\ResilientLoggerTasks;
use Drupal\helfi_api_base\AuditLog\Sources\HelfiAuditLogSource;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Http\Client\ClientInterface;
use ResilientLogger\Targets\ElasticsearchLogTarget;

/**
 * Tests the cron-driven ResilientLoggerTasks pipeline end-to-end.
 */
#[Group('helfi_api_base')]
#[RunTestsInSeparateProcesses]
class ResilientLoggerTasksTest extends KernelTestBase {

  use ApiTestTrait;

  private const int RETENTION_DAYS = 30;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('helfi_api_base', ['helfi_audit_logs']);
  }

  /**
   * Test that handleSubmitUnsentEntries ships rows to Elasticsearch.
   */
  public function testHandleSubmitUnsentEntriesShipsToElasticsearch(): void {
    $history = [];
    $guzzle = $this->createMockHistoryMiddlewareHttpClient($history, [
      new GuzzleResponse(201, [
        'Content-Type' => 'application/json',
        'X-Elastic-Product' => 'Elasticsearch',
      ], json_encode([
        '_index' => 'audit-test',
        '_id' => 'irrelevant',
        'result' => 'created',
      ], flags: JSON_THROW_ON_ERROR)),
    ]);
    $this->configureResilientLogger($guzzle);

    $this->container->get(AuditLogServiceInterface::class)->logOperation(new AuditLogEvent(
      operation: 'TEST_OP',
      message: 'OK',
      target: ['id' => '42'],
    ));

    $this->container->get(ResilientLoggerTasks::class)
      ->handleSubmitUnsentEntries(time());

    // The single seeded row was shipped over HTTP.
    $this->assertCount(1, $history);
    /** @var \Psr\Http\Message\RequestInterface $request */
    $request = $history[0]['request'];
    $this->assertSame('PUT', $request->getMethod());
    $this->assertStringStartsWith('/audit-test/_doc/', $request->getUri()->getPath());
    $this->assertStringContainsString('op_type=create', $request->getUri()->getQuery());

    $payload = json_decode((string) $request->getBody(), TRUE, flags: JSON_THROW_ON_ERROR);
    $this->assertSame('TEST_OP', $payload['audit_event']['operation']);
    $this->assertSame('helfi-audit-log-test', $payload['audit_event']['origin']);

    // The row was marked sent.
    $isSent = $this->container->get('database')
      ->select('helfi_audit_logs', 'h')
      ->fields('h', ['is_sent'])
      ->execute()
      ->fetchField();
    $this->assertSame('1', (string) $isSent);
  }

  /**
   * Test that handleClearSentEntries deletes old sent rows.
   */
  public function testHandleClearSentEntriesRespectsRetentionWindow(): void {
    $this->configureResilientLogger();

    $now = time();
    $oldTs = gmdate('Y-m-d H:i:s', $now - (self::RETENTION_DAYS * 2 * 86400));
    $newTs = gmdate('Y-m-d H:i:s', $now);

    $oldSentId = $this->insertRow($oldTs, 1);
    $oldUnsentId = $this->insertRow($oldTs, 0);
    $newSentId = $this->insertRow($newTs, 1);
    $newUnsentId = $this->insertRow($newTs, 0);

    $this->container->get(ResilientLoggerTasks::class)
      ->handleClearSentEntries(time());

    $remaining = array_map(
      'intval',
      $this->container->get('database')
        ->select('helfi_audit_logs', 'h')
        ->fields('h', ['id'])
        ->execute()
        ->fetchCol()
    );
    sort($remaining);

    $expected = [$oldUnsentId, $newSentId, $newUnsentId];
    sort($expected);

    $this->assertSame($expected, $remaining, 'Only the old-and-sent row should be deleted.');
    $this->assertNotContains($oldSentId, $remaining);
  }

  /**
   * Configure resilient logger, optionally with a mocked client.
   */
  private function configureResilientLogger(?ClientInterface $httpClient = NULL): void {
    $target = [
      'class' => ElasticsearchLogTarget::class,
      'es_url' => 'https://fake-es:9200',
      'es_username' => 'user',
      'es_password' => 'pass',
      'es_index' => 'audit-test',
      'required' => TRUE,
    ];
    if ($httpClient !== NULL) {
      $target['http_client'] = $httpClient;
    }

    new Settings(Settings::getAll() + [
      'resilient_logger' => [
        'sources' => [
          ['class' => HelfiAuditLogSource::class],
        ],
        'targets' => [$target],
        'environment' => 'test',
        'origin' => 'helfi-audit-log-test',
        'store_old_entries_days' => self::RETENTION_DAYS,
      ],
    ]);
  }

  /**
   * Inserts a row directly so created_at and is_sent can be controlled.
   */
  private function insertRow(string $createdAt, int $isSent): int {
    return (int) $this->container->get('database')
      ->insert('helfi_audit_logs')
      ->fields([
        'created_at' => $createdAt,
        'is_sent' => $isSent,
        'message' => '{"audit_event":{}}',
      ])
      ->execute();
  }

}

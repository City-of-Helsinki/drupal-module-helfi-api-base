<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\DestructableInterface;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Drupal\helfi_api_base\AuditLog\Sources\AuditLogSource;
use Drupal\helfi_api_base\Drush\Commands\AuditLogClearSentEntriesCommand;
use Drupal\helfi_api_base\Drush\Commands\AuditLogSubmitUnsentEntriesCommand;
use Drupal\helfi_api_base\Hook\AuditLogEntityHooks;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Http\Client\ClientInterface;
use ResilientLogger\ResilientLogger;
use ResilientLogger\Targets\ElasticsearchLogTarget;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests the audit log drush commands end-to-end.
 */
#[Group('helfi_api_base')]
#[RunTestsInSeparateProcesses]
class AuditLogCommandsTest extends KernelTestBase {

  use ApiTestTrait;

  private const int RETENTION_DAYS = 30;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'diff',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\helfi_api_base\HelfiApiBaseServiceProvider::register()
   */
  protected function bootKernel(): void {
    $this->setSetting('resilient_logger', $this->resilientLoggerSettings());
    parent::bootKernel();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('helfi_api_base', ['helfi_audit_logs']);
  }

  /**
   * Test that the submit command ships rows to Elasticsearch.
   */
  public function testSubmitUnsentEntriesShipsToElasticsearch(): void {
    $history = [];
    $client = $this->createMockHistoryMiddlewareHttpClient($history, [
      new GuzzleResponse(201, [
        'Content-Type' => 'application/json',
        // The Elasticsearch client refuses to parse responses that are
        // missing the product header.
        'X-Elastic-Product' => 'Elasticsearch',
      ], json_encode([
        '_index' => 'audit-test',
        '_id' => 'irrelevant',
        'result' => 'created',
      ], flags: JSON_THROW_ON_ERROR)),
    ]);
    $this->setSetting('resilient_logger', $this->resilientLoggerSettings($client));

    $this->logEvent();

    $tester = $this->executeCommand(new AuditLogSubmitUnsentEntriesCommand($this->getResilientLogger()));
    $tester->assertCommandIsSuccessful();

    // The single seeded row was shipped over HTTP.
    $this->assertCount(1, $history);
    $request = $history[0]['request'];
    $this->assertSame('PUT', $request->getMethod());
    $this->assertStringStartsWith('/audit-test/_doc/', $request->getUri()->getPath());

    $payload = json_decode((string) $request->getBody(), TRUE, flags: JSON_THROW_ON_ERROR);
    $this->assertSame('ENTITY_CREATE', $payload['audit_event']['operation']);
    $this->assertSame('helfi-audit-log-test', $payload['audit_event']['origin']);

    // The row was marked sent.
    $isSent = $this->container->get(Connection::class)
      ->select('helfi_audit_logs', 'h')
      ->fields('h', ['is_sent'])
      ->execute()
      ->fetchField();
    $this->assertSame('1', (string) $isSent);
  }

  /**
   * Test that the submit command fails when an entry cannot be submitted.
   */
  public function testSubmitUnsentEntriesFailsOnRejectedEntry(): void {
    $history = [];
    $client = $this->createMockHistoryMiddlewareHttpClient($history, [
      new GuzzleResponse(500, [
        'X-Elastic-Product' => 'Elasticsearch',
      ], 'Internal server error'),
    ]);
    $this->setSetting('resilient_logger', $this->resilientLoggerSettings($client));

    $this->logEvent();

    $tester = $this->executeCommand(new AuditLogSubmitUnsentEntriesCommand($this->getResilientLogger()));
    $this->assertSame(Command::FAILURE, $tester->getStatusCode());

    // The row was left unsent so it can be retried.
    $isSent = $this->container->get(Connection::class)
      ->select('helfi_audit_logs', 'h')
      ->fields('h', ['is_sent'])
      ->execute()
      ->fetchField();
    $this->assertSame('0', (string) $isSent);
  }

  /**
   * Test that the clear command deletes old sent rows.
   */
  public function testClearSentEntriesRespectsRetentionWindow(): void {
    $this->setSetting('resilient_logger', $this->resilientLoggerSettings());

    $now = time();
    $oldTs = gmdate('Y-m-d H:i:s', $now - (self::RETENTION_DAYS * 2 * 86400));
    $newTs = gmdate('Y-m-d H:i:s', $now);

    $database = $this->container->get(Connection::class);
    $query = $database
      ->insert('helfi_audit_logs')
      ->fields(['created_at', 'is_sent', 'message']);

    $query->values(['created_at' => $oldTs, 'is_sent' => 1, 'message' => '{"audit_event":{}}']);
    $query->values(['created_at' => $oldTs, 'is_sent' => 0, 'message' => '{"audit_event":{}}']);
    $query->values(['created_at' => $newTs, 'is_sent' => 1, 'message' => '{"audit_event":{}}']);
    $query->values(['created_at' => $newTs, 'is_sent' => 0, 'message' => '{"audit_event":{}}']);
    $query->execute();

    $tester = $this->executeCommand(new AuditLogClearSentEntriesCommand($this->getResilientLogger()));
    $tester->assertCommandIsSuccessful();

    $remaining = (int) $this->container->get(Connection::class)
      ->select('helfi_audit_logs', 'h')
      ->countQuery()
      ->execute()
      ->fetchField();

    // Only the old and sent message should be deleted.
    $this->assertSame(3, $remaining);
  }

  /**
   * Test that commands fail gracefully when the audit log is unconfigured.
   */
  public function testCommandsFailWhenAuditLogIsNotConfigured(): void {
    foreach ([
      new AuditLogSubmitUnsentEntriesCommand(),
      new AuditLogClearSentEntriesCommand(),
    ] as $command) {
      $tester = $this->executeCommand($command);

      $this->assertSame(Command::FAILURE, $tester->getStatusCode());
      $this->assertStringContainsString('The audit log is not configured.', $tester->getDisplay());
    }
  }

  /**
   * Gets the resilient logger service.
   */
  private function getResilientLogger(): ResilientLogger {
    $logger = $this->container->get(ResilientLogger::class);
    assert($logger instanceof ResilientLogger);

    return $logger;
  }

  /**
   * Logs an audit log event.
   */
  private function logEvent(): void {
    $service = $this->container->get(AuditLogServiceInterface::class);
    $service->logOperation(new AuditLogEvent(
      operation: AuditLogEntityHooks::ENTITY_CREATE,
      message: 'Foobar',
      target: ['id' => '42'],
    ));

    // Events are queued until the service is destructed (by
    // DrupalKernel::terminate() at the end of the request).
    $this->assertInstanceOf(DestructableInterface::class, $service);
    $service->destruct();
  }

  /**
   * Runs the given command and returns the tester.
   */
  private function executeCommand(Command $command): CommandTester {
    $tester = new CommandTester($command);
    $tester->execute([]);

    return $tester;
  }

  /**
   * Builds the resilient_logger settings.
   *
   * @return array<string, mixed>
   *   Resilient logger settings.
   */
  private function resilientLoggerSettings(?ClientInterface $httpClient = NULL): array {
    $target = [
      'class' => ElasticsearchLogTarget::class,
      'es_url' => 'https://fake-es:9200',
      'es_username' => 'user',
      'es_password' => 'pass',
      'es_index' => 'audit-test',
      'required' => TRUE,
    ];

    // The client library uses can be mocked with http_client setting.
    if ($httpClient !== NULL) {
      $target['http_client'] = $httpClient;
    }

    return [
      'sources' => [
        ['class' => AuditLogSource::class],
      ],
      'targets' => [$target],
      'environment' => 'test',
      'origin' => 'helfi-audit-log-test',
      'store_old_entries_days' => self::RETENTION_DAYS,
    ];
  }

}

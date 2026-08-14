<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\AuditLog;

use Drupal\Core\DestructableInterface;
use Drupal\helfi_api_base\AuditLog\AuditLogOperation;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the AuditLogService.
 */
#[Group('helfi_api_base')]
#[RunTestsInSeparateProcesses]
class AuditLogServiceTest extends KernelTestBase {

  /**
   * {@inheritDoc}
   */
  protected static $modules = [
    'diff',
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
   * Test that message is passed all the way to audit log service.
   */
  public function testDatabaseWrite(): void {
    // Dispatch audit log event.
    $service = $this->container->get(AuditLogServiceInterface::class);
    $service->logOperation(new AuditLogEvent(
      operation: AuditLogOperation::EntityCreate,
      message: 'SUCCESS',
      target: ['id' => '123'],
    ));

    // Events are only queued until the service is destructed (normally by
    // DrupalKernel::terminate() at the end of the request).
    $this->assertInstanceOf(DestructableInterface::class, $service);
    $service->destruct();

    // Read back the row that the event subscriber wrote to the database.
    $row = $this->container->get('database')
      ->select('helfi_audit_logs', 'al')
      ->fields('al', ['message'])
      ->execute()
      ->fetchAssoc();

    $this->assertNotEmpty($row);
    $auditEvent = json_decode($row['message'], TRUE)['audit_event'];

    $this->assertEquals('ENTITY_CREATE', $auditEvent['operation']);
    $this->assertEquals('SUCCESS', $auditEvent['message']);
    $this->assertEquals(['id' => '123'], $auditEvent['target']);

    // The actor is added generically by the AuditLogActorSubscriber.
    $this->assertEquals('anonymous', $auditEvent['actor']['role']);
    $this->assertArrayHasKey('id', $auditEvent['actor']);
    $this->assertArrayHasKey('ip_address', $auditEvent['actor']);
  }

}

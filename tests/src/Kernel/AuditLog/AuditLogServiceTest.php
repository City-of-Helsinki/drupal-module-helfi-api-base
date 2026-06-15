<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\AuditLog;

use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
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
    $this->container->get(AuditLogServiceInterface::class)->dispatchEvent([
      'key1' => 'value1',
      'key2' => 'value2',
    ]);

    // Read back the row that the event subscriber wrote to the database.
    $row = $this->container->get('database')
      ->select('helfi_audit_logs', 'al')
      ->fields('al', ['message'])
      ->execute()
      ->fetchAssoc();

    $this->assertNotEmpty($row);
    $auditEvent = json_decode($row['message'], TRUE)['audit_event'];

    $this->assertEquals('DRUPAL', $auditEvent['origin']);
    $this->assertEquals('DRUPAL', $auditEvent['source']);
    $this->assertEquals('value1', $auditEvent['key1']);
    $this->assertEquals('value2', $auditEvent['key2']);
  }

}

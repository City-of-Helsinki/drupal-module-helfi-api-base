<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\AuditLog;

use Drupal\Core\DestructableInterface;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Drupal\helfi_api_base\Hook\AuditLogEntityHooks;
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
      operation: AuditLogEntityHooks::ENTITY_CREATE,
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

  /**
   * Tests that messages larger than 64 KB are saved.
   */
  public function testLargeMessage(): void {
    $diff = str_repeat('a', 100000);
    $this->writeEvent($diff);

    $messages = $this->getMessages();
    $this->assertCount(1, $messages);
    $this->assertEquals($diff, json_decode($messages[0], TRUE)['audit_event']['extra']['ContentDiff']);
  }

  /**
   * Writes an audit log event with the given content diff.
   *
   * @param string $diff
   *   The content diff.
   */
  private function writeEvent(string $diff): void {
    $service = $this->container->get(AuditLogServiceInterface::class);
    $service->logOperation(new AuditLogEvent(
      operation: AuditLogEntityHooks::ENTITY_UPDATE,
      message: 'SUCCESS',
      target: ['id' => '123'],
      extra: ['ContentDiff' => $diff],
    ));
    $this->assertInstanceOf(DestructableInterface::class, $service);
    $service->destruct();
  }

  /**
   * Gets the saved audit log messages.
   *
   * @return string[]
   *   The messages.
   */
  private function getMessages(): array {
    return $this->container->get('database')
      ->select('helfi_audit_logs', 'al')
      ->fields('al', ['message'])
      ->execute()
      ->fetchCol();
  }

}

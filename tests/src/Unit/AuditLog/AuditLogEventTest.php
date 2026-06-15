<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\AuditLog;

use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests AuditLogEvent object.
 */
#[Group('helfi_api_base')]
class AuditLogEventTest extends UnitTestCase {

  /**
   * Test that event can be created with a default origin.
   */
  public function testCreateEvent() : void {
    $event = new AuditLogEvent(['operation' => 'TEST_OP']);
    $this->assertEquals('DRUPAL', $event->getOrigin());
    $this->assertEquals('TEST_OP', $event->getMessage()['operation']);
  }

  /**
   * Test that the event origin can be set via the constructor.
   */
  public function testEventOrigin() : void {
    $event = new AuditLogEvent(['operation' => 'TEST_OP'], 'TEST-ORIGIN');
    $this->assertEquals('TEST-ORIGIN', $event->getOrigin());
  }

  /**
   * Test that the event message is exposed as given.
   */
  public function testEventMessage() : void {
    $message = [
      'key1' => 'value1',
      'key2' => 'value2',
    ];
    $event = new AuditLogEvent($message);
    $this->assertSame($message, $event->getMessage());
    $this->assertEquals('value1', $event->getMessage()['key1']);
    $this->assertEquals('value2', $event->getMessage()['key2']);
  }

}

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
   * Test that event can be created.
   */
  public function testCreateEvent() : void {
    $event = new AuditLogEvent(['message']);
    $this->assertEquals($event->getOrigin(), 'DRUPAL');
    $this->assertEquals($event->isValid(), TRUE);
    $this->assertEquals($event->getMessage()[0], 'message');
  }

  /**
   * Test that event origin can be modified.
   */
  public function testModifyEventOrigin() : void {
    $event = new AuditLogEvent(['message']);
    $this->assertEquals($event->getOrigin(), 'DRUPAL');
    $event->setOrigin('TEST-MODIFY-EVENT');
    $this->assertEquals($event->getOrigin(), 'TEST-MODIFY-EVENT');
  }

  /**
   * Test that event message can be modified.
   */
  public function testModifyEventMessage() : void {
    $event = new AuditLogEvent(['message']);
    $this->assertArrayHasKey(0, $event->getMessage());
    $this->assertCount(1, $event->getMessage());
    $newMessage = [
      'key1' => 'value1',
      'key2' => 'value2',
    ];
    $event->setMessage($newMessage);
    $this->assertArrayNotHasKey(0, $event->getMessage());
    $this->assertArrayHasKey('key1', $event->getMessage());
    $this->assertEquals('value1', $event->getMessage()['key1']);
    $this->assertArrayHasKey('key2', $event->getMessage());
    $this->assertEquals('value2', $event->getMessage()['key2']);
    $this->assertCount(2, $event->getMessage());
  }

  /**
   * Test that event validity can be modified.
   */
  public function testModifyEventValidity() : void {
    $event = new AuditLogEvent(['message']);
    $this->assertEquals($event->isValid(), TRUE);
    $event->setValid(FALSE);
    $this->assertEquals($event->isValid(), FALSE);
  }

}

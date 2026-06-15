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
    $event = new AuditLogEvent('TEST_OP', 'SUCCESS', ['id' => '1']);
    $this->assertEquals('DRUPAL', $event->getOrigin());
    $this->assertEquals('TEST_OP', $event->getData()['operation']);
    $this->assertEquals('SUCCESS', $event->getData()['message']);
    $this->assertEquals(['id' => '1'], $event->getData()['target']);
  }

  /**
   * Test that the event origin can be set via the constructor and overridden.
   */
  public function testEventOrigin() : void {
    $event = new AuditLogEvent('TEST_OP', 'SUCCESS', [], origin: 'TEST-ORIGIN');
    $this->assertEquals('TEST-ORIGIN', $event->getOrigin());

    $event->setOrigin('OTHER-ORIGIN');
    $this->assertEquals('OTHER-ORIGIN', $event->getOrigin());
  }

  /**
   * Test that the actor is only included in the data once set.
   */
  public function testEventActor() : void {
    $event = new AuditLogEvent('TEST_OP', 'SUCCESS', []);
    $this->assertArrayNotHasKey('actor', $event->getData());

    $actor = ['role' => 'USER', 'user_id' => '123'];
    $event->setActor($actor);
    $this->assertSame($actor, $event->getData()['actor']);
  }

}

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
   * Test that event data can be retrieved.
   */
  public function testCreateEvent() : void {
    $event = new AuditLogEvent('TEST_OP', 'SUCCESS', ['id' => '1']);
    $this->assertEquals('TEST_OP', $event->getData()['operation']);
    $this->assertEquals('SUCCESS', $event->getData()['message']);
    $this->assertEquals(['id' => '1'], $event->getData()['target']);
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

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Azure\PubSub;

use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Azure\PubSub\PubSubMessage;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Azure\PubSub\PubSubMessage
 * @group helfi_api_base
 */
class PubSubMessageTest extends UnitTestCase {

  /**
   * @covers ::__construct
   * @covers ::__toString
   */
  public function testMessage() : void {
    $data = ['test' => 'something'];
    $sut = new PubSubMessage($data);
    $this->assertSame($data, $sut->data);
    $this->assertSame('{"test":"something"}', (string) $sut);
  }

}

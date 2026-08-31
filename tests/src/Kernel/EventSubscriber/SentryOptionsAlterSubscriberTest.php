<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\EventSubscriber;

use Drupal\KernelTests\KernelTestBase;
use Drupal\raven\Event\OptionsAlter;
use Sentry\Event;

/**
 * Tests our custom options alter event subscriber.
 */
class SentryOptionsAlterSubscriberTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'raven',
    'diff',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('helfi_api_base');
  }

  /**
   * Test that ignoring errors work.
   */
  public function testOptionsAlter(): void {
    $dispatcher = $this->container->get('event_dispatcher');

    $options = [];
    $event = new OptionsAlter($options);
    $dispatcher->dispatch($event);

    $this->assertArrayHasKey('before_send', $event->options);
    $this->assertIsCallable($event->options['before_send']);

    $sentry_event = Event::createEvent();

    // Test that ignored error is ignored.
    $sentry_event->setMessage('', [], 'No alive nodes. All the 1 nodes seem to be down');
    $result = ($event->options['before_send'])($sentry_event);
    $this->assertNull($result);

    // Test message which is not in ignore list.
    $sentry_event = Event::createEvent();
    $sentry_event->setMessage('', [], 'This should not be ignored (result not null)');
    $result = ($event->options['before_send'])($sentry_event);
    $this->assertSame($sentry_event, $result);
  }

}

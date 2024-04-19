<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\EventSubscriber;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use Sentry\SentrySdk;

/**
 * Tests our custom Sentry 'traces_sampler' event subscriber.
 *
 * @group helfi_api_base
 */
class SentryTracesSamplerSubscriberTest extends KernelTestBase {

  use ApiTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'raven',
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
   * Make sure our 'traces_sampler' is actually registered.
   */
  public function testSamplerIsCalled() : void {
    $request = $this->getMockedRequest('/user/login');
    $this->processRequest($request);

    $client = SentrySdk::getCurrentHub()->getClient();
    $sampler = $client->getOptions()->getTracesSampler();
    $this->assertIsCallable($sampler);
  }

}

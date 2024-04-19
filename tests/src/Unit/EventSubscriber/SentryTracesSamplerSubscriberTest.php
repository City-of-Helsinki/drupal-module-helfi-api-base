<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\EventSubscriber;

use Drupal\helfi_api_base\EventSubscriber\SentryTracesSamplerSubscriber;
use Drupal\raven\Event\OptionsAlter;
use Drupal\Tests\UnitTestCase;
use Sentry\Tracing\SamplingContext;
use Sentry\Tracing\TransactionContext;

/**
 * Tests sentry traces sample.
 *
 * @group helfi_api_base
 */
class SentryTracesSamplerSubscriberTest extends UnitTestCase {

  /**
   * Calls the sampler callback.
   *
   * @param array $options
   *   The default options.
   * @param \Sentry\Tracing\SamplingContext $context
   *   The sampling context.
   *
   * @return float
   *   The sample rate.
   */
  private function callSampler(array $options, SamplingContext $context) : float {
    $sut = new SentryTracesSamplerSubscriber();
    $sut->setTracesSampler(new OptionsAlter($options));

    $this->assertIsCallable($options['traces_sampler']);

    return $options['traces_sampler']($context);
  }

  /**
   * Tests the default sample rate value.
   */
  public function testDefaultSamplerValue() : void {
    $rate = $this->callSampler([], new SamplingContext());
    $this->assertEquals(SentryTracesSamplerSubscriber::DEFAULT_SAMPLE_RATE, $rate);
  }

  /**
   * Make sure the sample rate is inherited from parent.
   */
  public function testParentSampledValue() : void {
    $rate = $this->callSampler([], (new SamplingContext())->setParentSampled(TRUE));
    $this->assertEquals(1.0, $rate);
  }

  /**
   * Make sure the sample rate is inherited from the setting.
   */
  public function testParentDefaultValue() : void {
    $rate = $this->callSampler(['traces_sample_rate' => 0.5], (new SamplingContext()));
    $this->assertEquals(0.5, $rate);
  }

  /**
   * Make sure nothing is done when URL context is not set.
   */
  public function testTracerNoUrl() : void {
    $transaction = new TransactionContext();
    $transaction->setData([]);
    $context = SamplingContext::getDefault($transaction);
    $rate = $this->callSampler([], $context);
    $this->assertEquals(SentryTracesSamplerSubscriber::DEFAULT_SAMPLE_RATE, $rate);
  }

  /**
   * Make sure the sample rate is set zero when we get a URL match.
   *
   * @dataProvider ignoreUrlData
   */
  public function testTracerIgnoreUrl(string $url) : void {
    $transaction = new TransactionContext();
    $transaction->setData(['http.url' => $url]);
    $context = SamplingContext::getDefault($transaction);
    $rate = $this->callSampler([], $context);
    $this->assertEquals(0, $rate);
  }

  /**
   * Data provider.
   *
   * @return array[]
   *   The data.
   */
  public function ignoreUrlData() : array {
    return [
      ['http://localhost/fi/health'],
      ['https://localhost/health'],
    ];
  }

}

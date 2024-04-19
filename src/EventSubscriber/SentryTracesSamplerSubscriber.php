<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\raven\Event\OptionsAlter;
use Sentry\Tracing\SamplingContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Overrides the Sentry traces sampler to ignore certain data.
 */
final class SentryTracesSamplerSubscriber implements EventSubscriberInterface {

  public const DEFAULT_SAMPLE_RATE = 0.2;

  /**
   * Checks if the trace url should be ignored.
   *
   * @param array $data
   *   The data.
   *
   * @return bool
   *   TRUE if url should be ignored.
   */
  private function ignoreTracerUrl(array $data) : bool {
    if (!isset($data['http.url'])) {
      return FALSE;
    }
    $path = parse_url($data['http.url'], PHP_URL_PATH);

    return str_ends_with($path, '/health');
  }

  /**
   * Responds to OptionsAlter event.
   *
   * @param \Drupal\raven\Event\OptionsAlter $event
   *   The options alter event.
   */
  public function setTracesSampler(OptionsAlter $event) : void {
    $event->options['traces_sampler'] = function (SamplingContext $context) use ($event): float {
      if ($context->getParentSampled()) {
        // If the parent transaction (for example, a JavaScript front-end)
        // is sampled, also sample the current transaction.
        return 1.0;
      }

      $data = $context->getTransactionContext()?->getData();

      if ($data && $this->ignoreTracerUrl($data)) {
        return 0;
      }

      // Sample ~20% of transactions by default.
      // @see https://docs.sentry.io/platforms/php/configuration/sampling/
      return $event->options['traces_sample_rate'] ?? self::DEFAULT_SAMPLE_RATE;
    };
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      'Drupal\raven\Event\OptionsAlter' => ['setTracesSampler'],
    ];
  }

}

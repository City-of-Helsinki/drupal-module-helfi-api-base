<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\raven\Event\OptionsAlter;
use Sentry\Event;
use Sentry\Tracing\SamplingContext;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Allow altering sentry errors before sending.
 */
final class SentryOptionsAlterEventSubscriber implements EventSubscriberInterface {

  public const float DEFAULT_SAMPLE_RATE = 0.2;

  public function __construct(
    #[AutowireServiceClosure(service: ConfigFactoryInterface::class)] private readonly \Closure $configFactoryClosure,
  ) {
  }

  /**
   * Checks if the trace url should be ignored.
   *
   * @param array<string, mixed> $data
   *   The data.
   *
   * @return bool
   *   TRUE if url should be ignored.
   */
  private static function ignoreTracerUrl(array $data) : bool {
    if (!isset($data['http.url'])) {
      return FALSE;
    }
    $path = parse_url($data['http.url'], PHP_URL_PATH);

    if (!is_string($path)) {
      return FALSE;
    }
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

      if ($data && self::ignoreTracerUrl($data)) {
        return 0;
      }

      // Sample ~20% of transactions by default.
      // @see https://docs.sentry.io/platforms/php/configuration/sampling/
      return $event->options['traces_sample_rate'] ?? self::DEFAULT_SAMPLE_RATE;
    };
  }

  /**
   * Alter the Sentry client options.
   */
  public function filterErrors(OptionsAlter $optionsAlterEvent) : void {
    $errors = ($this->configFactoryClosure)()->get('helfi_api_base.settings')->get('sentry_errors');
    if (!$errors) {
      return;
    }

    $optionsAlterEvent->options['before_send'] = function (Event $event) use ($errors): ?Event {
      $eventErrorMessage = $event->getMessageFormatted() ?? '';

      $ignore = $errors['ignore'] ?? [];
      // Ignore errors.
      if (array_any($ignore, fn($message) => str_contains($eventErrorMessage, $message))) {
        return NULL;
      }

      $sample = $errors['sample'] ?? [];
      // Handle rate limited errors.
      foreach ($sample as $message => $rateLimit) {
        if (str_contains($eventErrorMessage, $message) && $this->skipErrorByRateLimit($rateLimit)) {
          return NULL;
        }
      }

      return $event;
    };
  }

  /**
   * Limit the amount of errors sent.
   *
   * @param float $rate
   *   The amount of errors to send to sentry.
   *
   * @return bool
   *   Error should be skipped.
   */
  private function skipErrorByRateLimit(float $rate): bool {
    // If the random float is bigger than given limit, skip the error.
    // phpcs:ignore
    return (mt_rand() / mt_getrandmax()) > $rate; // NOSONAR.
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      OptionsAlter::class => [
        ['filterErrors'],
        ['setTracesSampler'],
      ],
    ];
  }

}

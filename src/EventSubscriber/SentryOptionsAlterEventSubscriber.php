<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\raven\Event\OptionsAlter;
use Sentry\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Allow altering sentry errors before sending.
 */
final class SentryOptionsAlterEventSubscriber implements EventSubscriberInterface {

  /**
   * Information is used by Sentry to group errors.
   *
   * @var array|string[]
   */
  private array $fingerprintRules = [
    'error.type:"*" -> group-by-exception-then-message, #{{ error.type }}, #{{ error.value }}',
  ];

  /**
   * List of errors to ignore.
   *
   * @var array|string[]
   */
  private array $ignoredErrors = [
    'No alive nodes. All the 1 nodes seem to be down',
  ];

  /**
   * Array of sample rates.
   *
   * @var array|string[]
   */
  private array $sampleRates = [
    'cURL error 6: Could not resolve host: helfi-etusivu' => 0.1,
  ];

  /**
   * Alter the Sentry client options.
   */
  public function alterOptions(OptionsAlter $optionsAlterEvent) : void {
    $optionsAlterEvent->options['before_send'] = function (Event $event): ?Event {
      $eventErrorMessage = $event->getMessageFormatted();

      // Alter fingerprint: Fingerprint is used by Sentry to group errors.
      $event->setFingerprint($this->fingerprintRules);

      // Ignore errors.
      if (array_any($this->ignoredErrors, fn($message) => str_contains($eventErrorMessage, $message))) {
        return NULL;
      }



      // Handle rate limited errors.
      foreach ($this->sampleRates as $message => $rateLimit) {
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
    return (mt_rand() / mt_getrandmax()) > $rate;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      OptionsAlter::class => ['alterOptions'],
    ];
  }

}

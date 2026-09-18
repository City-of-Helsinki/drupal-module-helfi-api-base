<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\raven\Event\OptionsAlter;
use Sentry\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Allow altering sentry errors before sending.
 */
final class SentryOptionsAlterEventSubscriber implements EventSubscriberInterface {

  public function __construct(private ConfigFactoryInterface $configFactory) {
  }

  /**
   * Alter the Sentry client options.
   */
  public function alterOptions(OptionsAlter $optionsAlterEvent) : void {
    $errors = $this->configFactory->get('helfi_api_base.settings')->get('sentry_errors');
    $optionsAlterEvent->options['before_send'] = function (Event $event) use ($errors): ?Event {
      $eventErrorMessage = $event->getMessageFormatted() ?? '';

      // Ignore errors.
      if (array_any($errors['ignore'], fn($message) => str_contains($eventErrorMessage, $message))) {
        return NULL;
      }

      // Handle rate limited errors.
      foreach ($errors['sample'] as $message => $rateLimit) {
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
      OptionsAlter::class => ['alterOptions'],
    ];
  }

}

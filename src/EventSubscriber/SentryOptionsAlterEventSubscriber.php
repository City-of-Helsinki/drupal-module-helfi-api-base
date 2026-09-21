<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\raven\Event\OptionsAlter;
use Sentry\Event;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Allow altering sentry errors before sending.
 */
final class SentryOptionsAlterEventSubscriber implements EventSubscriberInterface {

  public function __construct(
    #[AutowireServiceClosure(service: ConfigFactoryInterface::class)] private readonly \Closure $configFactoryClosure,
  ) {
  }

  /**
   * Alter the Sentry client options.
   */
  public function alterOptions(OptionsAlter $optionsAlterEvent) : void {
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
      OptionsAlter::class => ['alterOptions'],
    ];
  }

}

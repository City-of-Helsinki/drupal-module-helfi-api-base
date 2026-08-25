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
   * Alter the Sentry client options.
   */
  public function alterOptions(OptionsAlter $optionsAlterEvent) : void {
    $optionsAlterEvent->options['before_send'] = function (Event $event): ?Event {
      // Alter fingerprint: Fingerprint is used by Sentry to group errors.
      $fingerprints = [
        'error.type:"*" -> group-by-exception-then-message, #{{ error.type }}, #{{ error.value }}'
      ];
      $event->setFingerprint($fingerprints);

      // Exclude error messages.
      $excludeByMessage = [
        'No alive nodes. All the 1 nodes seem to be down',
        'cURL error 6: Could not resolve host: helfi-etusivu',
      ];
      if (array_any($excludeByMessage, fn($message) => str_contains($event->getMessageFormatted(), $message))) {
        return NULL;
      }

      return $event;
    };
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

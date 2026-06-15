<?php

declare (strict_types=1);

namespace Drupal\helfi_api_base\AuditLog\EventSubscriber;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds the acting user to audit log events.
 *
 * Projects can subscribe at a lower priority to refine the actor,
 * for example to replace the user id with an external identifier.
 */
readonly class AuditLogActorSubscriber implements EventSubscriberInterface {

  public function __construct(
    private AccountProxyInterface $currentUser,
    private RequestStack $requestStack,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      AuditLogEvent::class => ['addActor', 10],
    ];
  }

  /**
   * Adds the acting user to the event.
   *
   * @param \Drupal\helfi_api_base\AuditLog\Event\AuditLogEvent $event
   *   The audit log event.
   */
  public function addActor(AuditLogEvent $event): void {
    if (empty($this->actor)) {
      $event->setActor([
        'role' => implode(',', $this->currentUser->getRoles()),
        'user_id' => $this->currentUser->id(),
        'ip_address' => $this->requestStack->getCurrentRequest()?->getClientIp(),
      ]);
    }
  }

}

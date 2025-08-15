<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Session\AccountEvents;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountSetEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Listens user events.
 */
class UserLoginSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new instance.
   */
  public function __construct(
    private readonly RequestStack $requestStack,
    #[Autowire(param: 'helfi_api_base.restricted_roles')]
    private readonly array $restrictedRoles,
    #[Autowire(service: 'logger.channel.helfi_api_base')]
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      AccountEvents::SET_USER => 'onUserLogin',
    ];
  }

  /**
   * Reacts to `account.set` event.
   *
   * @param \Drupal\Core\Session\AccountSetEvent $event
   *   The event.
   */
  public function onUserLogin(AccountSetEvent $event): void {
    $account = $event->getAccount();

    if (!$this->applies($account)) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();
    $clientIp = $request->getClientIp();

    if (!$clientIp || !IpUtils::checkIp($clientIp, IpUtils::PRIVATE_SUBNETS)) {
      $this->logger->warning('Login attempt denied for @user from @ip.', [
        '@user' => $account->getAccountName(),
        '@ip' => $clientIp,
      ]);

      user_logout();
    }
  }

  /**
   * Returns true if login is restricted for the given account.
   */
  private function applies(AccountInterface $account): bool {
    if ($account->isAnonymous()) {
      return FALSE;
    }

    if (array_intersect($account->getRoles(TRUE), $this->restrictedRoles)) {
      return TRUE;
    }

    return FALSE;
  }

}

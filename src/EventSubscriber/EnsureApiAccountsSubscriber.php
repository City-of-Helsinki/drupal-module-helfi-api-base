<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\user\Entity\Role;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Handles post deploy tasks.
 */
final class EnsureApiAccountsSubscriber extends DeployHookEventSubscriberBase {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private MessengerInterface $messenger,
    private ConfigFactoryInterface $configFactory,
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * This is used to ensure that API accounts always retain the same
   * credentials.
   */
  public function onPostDeploy(Event $event) : void {
    $accounts = $this->configFactory
      ->get('helfi_api_base.api_accounts')
      ->get('accounts');

    /** @var \Drupal\user\UserStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('user');

    foreach ($accounts as $account) {
      if (!isset($account['roles'])) {
        $account['roles'] = [];
      }

      [
        'username' => $username,
        'password' => $password,
        'roles' => $roles,
      ] = $account;

      $this->messenger
        ->addMessage(
          sprintf('[helfi_api_base]: %s found. Resetting password.', $username)
        );
      /** @var \Drupal\user\UserInterface $user */
      if (!$user = user_load_by_name($username)) {
        $this->messenger
          ->addMessage(
            sprintf('[helfi_api_base]: Account %s not found. Creating a new account.', $username)
          );
        $user = $storage->create([
          'name' => $username,
        ]);
      }
      foreach ($roles as $role) {
        if (!Role::load($role)) {
          $this->messenger
            ->addError(sprintf('Role %s not found. Skipping.', $role));
        }
        $user->addRole($role);
      }
      $user->setPassword($password)
        ->save();
    }
  }

}

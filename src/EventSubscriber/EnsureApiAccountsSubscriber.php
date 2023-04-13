<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Handles post deploy tasks.
 */
final class EnsureApiAccountsSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private MessengerInterface $messenger,
  ) {
  }

  /**
   * Responds to 'helfi_api_base.post_deploy' event.
   *
   * This is used to ensure that API accounts always retain the same
   * credentials.
   *
   * @param \Symfony\Contracts\EventDispatcher\Event $event
   *   The event.
   */
  public function onPostDeploy(Event $event) : void {
    $accounts = [];

    foreach (getenv() as $key => $value) {
      // Scan for ENV variables starting with DRUPAL_ and ending with _API_KEY.
      // For example 'DRUPAL_NAVIGATION_API_KEY'.
      if (!str_starts_with($key, 'DRUPAL_') || !str_ends_with($key, '_API_KEY')) {
        continue;
      }
      $accounts[$key] = $value;
    }

    /** @var \Drupal\user\UserStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('user');

    foreach ($accounts as $env => $value) {
      [$username, $password] = explode(':', base64_decode($value));

      $this->messenger
        ->addMessage(
          sprintf('[helfi_api_base]: %s found. Resetting password...', $env)
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
      $user->setPassword($password)
        ->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      'helfi_api_base.post_deploy' => ['onPostDeploy'],
    ];
  }

}

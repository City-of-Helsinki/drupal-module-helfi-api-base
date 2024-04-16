<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\helfi_api_base\Features\FeatureManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Sets given users' password to NULL.
 *
 * This should prevent given users from logging in using password.
 */
final class DisableUserPasswordSubscriber extends DeployHookEventSubscriberBase {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Features\FeatureManager $featureManager
   *   The feature manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param array $users
   *   The users array.
   */
  public function __construct(
    private readonly FeatureManager $featureManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    #[Autowire('%helfi_api_base.disable_password_users%')] private readonly array $users = [],
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function onPostDeploy(Event $event) : void {
    if (!$this->featureManager->isEnabled(FeatureManager::DISABLE_USER_PASSWORD)) {
      return;
    }

    $storage = $this->entityTypeManager->getStorage('user');
    $query = $storage
      ->getQuery();
    $query
      ->condition('pass', NULL, 'IS NOT NULL');

    // Support loading users by uid, name or email.
    $userCondition = $query->orConditionGroup()
      ->condition('mail', $this->users, 'IN')
      ->condition('name', $this->users, 'IN')
      ->condition('uid', $this->users, 'IN');
    $query->condition($userCondition);

    $ids = $query->accessCheck(FALSE)
      ->execute();

    foreach ($ids as $id) {
      /** @var \Drupal\user\UserInterface $account */
      $account = $storage->load($id);
      $account->setPassword(NULL)
        ->save();
    }
  }

}

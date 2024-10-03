<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\UserExpire;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A class to expire users automatically.
 */
final class UserExpireManager {

  /**
   * Expire time in seconds (six months).
   */
  public const DEFAULT_EXPIRE = 15638400;

  public const LEEWAY = 86400;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time interface.
   */
  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private TimeInterface $time,
  ) {
  }

  /**
   * Loads and cancels the expired users.
   */
  public function cancelExpiredUsers() : void {
    $storage = $this->entityTypeManager->getStorage('user');

    foreach ($this->getExpiredUserIds() as $uid) {
      $account = $storage->load($uid);

      $account->block()
        ->save();
    }
  }

  /**
   * Gets the expired user ids.
   *
   * @return array<int, string>
   *   An array of user ids.
   */
  public function getExpiredUserIds() : array {
    $expireDate = ($this->time->getCurrentTime() - self::DEFAULT_EXPIRE);

    $query = $this->entityTypeManager->getStorage('user')
      ->getQuery();
    $query->accessCheck(FALSE);
    // Load users that have logged in at some point (access > 0), but
    // the access time is less than (current time - expire time).
    $accessCondition = $query->andConditionGroup()
      ->condition('access', 0, '>')
      ->condition('access', $expireDate, '<=');
    // Load users that have never logged in (access=0), and the
    // created time is less than (current time - expire time).
    $createdCondition = $query->andConditionGroup()
      ->condition('access', 0)
      ->condition('created', $expireDate, '<=');
    $expireCondition = $query->orConditionGroup()
      ->condition($accessCondition)
      ->condition($createdCondition);

    // Give recently 'changed' users some leeway until the account
    // is blocked again. This is used to make sure the account does not
    // immediately get blocked again after the account is unblocked.
    $leeway = ($this->time->getCurrentTime() - self::LEEWAY);

    $query
      ->condition($expireCondition)
      ->condition('status', 1)
      ->condition('changed', $leeway, '<=')
      ->addTag('expired_users')
      // Make sure we have an upper bound.
      ->range(0, 50);

    return $query->execute();
  }

}

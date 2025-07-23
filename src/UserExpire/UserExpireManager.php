<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\UserExpire;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A class to expire/delete users automatically.
 */
final class UserExpireManager {

  /**
   * Expire time in seconds (six months).
   */
  public const DEFAULT_EXPIRE = 15638400;

  /**
   * Delete time in seconds (~5 years).
   */
  public const DEFAULT_DELETE = 157680000;

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
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly TimeInterface $time,
  ) {
  }

  /**
   * Loads and deletes the expired users.
   */
  public function deleteExpiredUsers() : void {
    $storage = $this->entityTypeManager->getStorage('user');

    $queryFilter = new QueryFilter(
      expire: self::DEFAULT_DELETE,
      // Only load already blocked users.
      status: 0,
      // Use different query tag for deletion so Tunnistamo
      // users are included as well.
      // @see helfi_tunnistamo_query_expired_users_alter().
      queryTag: 'delete_expired_users',
    );
    foreach ($this->getExpiredUserIds($queryFilter) as $uid) {
      $storage->load($uid)
        ->delete();
    }
  }

  /**
   * Loads and cancels the expired users.
   */
  public function cancelExpiredUsers() : void {
    $storage = $this->entityTypeManager->getStorage('user');

    $queryFilter = new QueryFilter(
      expire: self::DEFAULT_EXPIRE,
      status: 1,
      queryTag: 'expired_users',
    );
    foreach ($this->getExpiredUserIds($queryFilter) as $uid) {
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
  private function getExpiredUserIds(QueryFilter $queryFilter) : array {
    $expireDate = ($this->time->getCurrentTime() - $queryFilter->expire);

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

    if ($queryFilter->queryTag) {
      $query->addTag($queryFilter->queryTag);
    }
    $query
      ->condition($expireCondition)
      ->condition('changed', $leeway, '<=')
      ->condition('status', $queryFilter->status)
      // Make sure we have an upper bound.
      ->range(0, 50);

    return $query->execute();
  }

}

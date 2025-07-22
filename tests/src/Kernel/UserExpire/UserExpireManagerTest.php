<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\UserExpire;

use Drupal\helfi_api_base\UserExpire\QueryFilter;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\helfi_api_base\Features\FeatureManager;
use Drupal\helfi_api_base\UserExpire\UserExpireManager;
use Drupal\user\Entity\User;

/**
 * Tests user expiration feature.
 *
 * @group helfi_api_base
 */
class UserExpireManagerTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'system',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->installSchema('user', ['users_data']);
    $this->installEntitySchema('user');
    $this->installConfig(['helfi_api_base']);
  }

  /**
   * Gets the SUT.
   *
   * @return \Drupal\helfi_api_base\UserExpire\UserExpireManager
   *   The SUT.
   */
  public function getSut() : UserExpireManager {
    return $this->container->get(UserExpireManager::class);
  }

  /**
   * Tests cron user removal.
   */
  public function testCron() : void {
    $user = $this->createUser();
    $this->assertFalse($user->isBlocked());
    $user->setLastAccessTime(strtotime('-7 months'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();

    // Make sure the user is canceled when the cron is run.
    helfi_api_base_cron();
    $this->assertTrue(User::load($user->id())->isBlocked());
  }

  /**
   * Make sure the user is not blocked when the feature is disabled.
   */
  public function testCronFeatureDisabled(): void {
    /** @var \Drupal\helfi_api_base\Features\FeatureManager $service */
    $service = $this->container->get(FeatureManager::class);
    $service->disableFeature(FeatureManager::USER_EXPIRE);

    $user = $this->createUser();
    $this->assertFalse($user->isBlocked());
    $user->setLastAccessTime(strtotime('-7 months'))
      ->save();

    // Make sure the user is canceled when the cron is run.
    helfi_api_base_cron();
    $this->assertFalse(User::load($user->id())->isBlocked());
  }

  /**
   * Tests the expired users.
   */
  public function testExpiredUsers() : void {
    /** @var \Drupal\user\UserInterface[] $users */
    $users = [
      '1' => $this->createUser(),
      '2' => $this->createUser(),
      '3' => $this->createUser(),
      '4' => $this->createUser(),
    ];

    foreach ($users as $user) {
      // Make sure users have never logged in.
      $this->assertEquals(0, $user->getLastAccessedTime());
      $this->assertTrue($user->getCreatedTime() > 0);
    }

    $expired = $this->getSut()
      ->getExpiredUserIds(
        new QueryFilter(
          expire: UserExpireManager::DEFAULT_EXPIRE,
          status: 1,
        )
      );
    $this->assertEmpty($expired);

    // Set access time below the expiry threshold.
    $users['1']->setLastAccessTime(strtotime('-1 months'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();
    // Set access time over the expiry threshold.
    $users['2']->setLastAccessTime(strtotime('-7 months'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();
    // Set changed time below expiry threshold to make sure
    // users have some leeway.
    $users['4']->setLastAccessTime(strtotime('-1 months'))
      ->setChangedTime(strtotime('now'))
      ->save();

    $expired = $this->getSut()
      ->getExpiredUserIds(
        new QueryFilter(
          expire: UserExpireManager::DEFAULT_EXPIRE,
          status: 1,
        )
      );
    $this->assertEquals([2 => 2], $expired);

    // Set created time over the expiry threshold.
    $users['3']->set('created', strtotime('-7 months'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();

    $expired = $this->getSut()
      ->getExpiredUserIds(
        new QueryFilter(
          expire: UserExpireManager::DEFAULT_EXPIRE,
          status: 1,
        )
      );
    $this->assertEquals([2 => 2, 3 => 3], $expired);

    $this->getSut()->cancelExpiredUsers();

    // Users 2 and 3 should be blocked.
    $this->assertFalse(User::load(1)->isBlocked());
    $this->assertFalse(User::load(4)->isBlocked());
    $this->assertTrue(User::load(2)->isBlocked());
    $this->assertTrue(User::load(3)->isBlocked());

    // Set access time over the delete threshold.
    User::load(2)->setLastAccessTime(strtotime('-5 years 1 day'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();
    // Set created time over the delete threshold.
    User::load(3)->set('created', strtotime('-5 years 1 day'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();

    $this->getSut()->deleteExpiredUsers();

    // Users 2 and 3 should be deleted now.
    $this->assertNull(User::load(2));
    $this->assertNull(User::load(3));
  }

}

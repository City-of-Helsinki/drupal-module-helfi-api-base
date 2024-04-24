<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\UserExpire;

use Drupal\helfi_api_base\UserExpire\UserExpireManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;

/**
 * Tests user expiration feature.
 *
 * @group helfi_api_base
 */
class UserExpireTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

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
   * Tests the expired users.
   */
  public function testExpiredUsers() : void {
    /** @var \Drupal\user\UserInterface[] $users */
    $users = [
      '1' => $this->createUser(),
      '2' => $this->createUser(),
      '3' => $this->createUser(),
    ];

    foreach ($users as $user) {
      // Make sure users have never logged in.
      $this->assertEquals(0, $user->getLastAccessedTime());
      $this->assertTrue($user->getCreatedTime() > 0);
    }

    $expired = $this->getSut()->getExpiredUserIds();
    $this->assertEmpty($expired);

    // Set access time below the threshold.
    $users['1']->setLastAccessTime(strtotime('-1 months'))
      ->save();
    // Set access time over the threshold.
    $users['2']->setLastAccessTime(strtotime('-7 months'))
      ->save();

    $expired = $this->getSut()->getExpiredUserIds();
    $this->assertEquals([2 => 2], $expired);

    // Set created time over the threshold.
    $users['3']->set('created', strtotime('-7 months'))
      ->save();

    $expired = $this->getSut()->getExpiredUserIds();
    $this->assertEquals([2 => 2, 3 => 3], $expired);

    $this->getSut()->cancelExpiredUsers();

    // Users 2 and 3 should be blocked.
    $this->assertFalse(User::load(1)->isBlocked());
    $this->assertTrue(User::load(2)->isBlocked());
    $this->assertTrue(User::load(3)->isBlocked());
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\UserExpire;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\NodeCreationTrait;
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
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'system',
    'node',
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
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $node_type = NodeType::create([
      'type' => 'page',
      'name' => 'Page',
    ]);
    $node_type->save();
  }

  /**
   * Asserts that the given users have been blocked.
   *
   * @param array $expected
   *   The expected users.
   * @param \Drupal\user\UserInterface[] $users
   *   The users.
   */
  private function assertExpiredUsers(array $expected, array $users) : void {
    $expired = array_keys(
      array_filter($users, fn ($user) => User::load($user->id())->isBlocked())
    );

    $this->assertCount(count($expected), $expired);
    $this->assertEquals($expected, $expired);
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
    $users = [
      '1' => $this->createUser(),
      '2' => $this->createUser(),
    ];

    foreach ($users as $user) {
      $this->assertFalse($user->isBlocked());
      $user->setLastAccessTime(strtotime('-7 months'))
        ->setChangedTime(strtotime('-2 days'))
        ->save();
    }

    // Make sure the user is canceled when the cron is run.
    helfi_api_base_cron();
    // Make sure uid 1 user is not blocked.
    $this->assertFalse(User::load($users['1']->id())->isBlocked());
    $this->assertEquals(1, $users['1']->id());
    $this->assertTrue(User::load($users['2']->id())->isBlocked());
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

    // Make sure the user is not canceled when the cron is run.
    helfi_api_base_cron();
    $this->assertFalse(User::load($user->id())->isBlocked());
  }

  /**
   * Tests the expired users.
   */
  public function testExpiredUsers() : void {
    // Create UID 1 user.
    $this->createUser();

    /** @var \Drupal\user\UserInterface[] $users */
    $users = [
      '2' => $this->createUser(),
      '3' => $this->createUser(),
      '4' => $this->createUser(),
      '5' => $this->createUser(),
    ];

    foreach ($users as $user) {
      // Make sure we don't have an account with UID 1.
      $this->assertTrue($user->id() > 1);
      // Make sure users have never logged in.
      $this->assertEquals(0, $user->getLastAccessedTime());
      $this->assertTrue($user->getCreatedTime() > 0);
    }

    $this->getSut()->cancelExpiredUsers();

    // Make sure no users are canceled.
    foreach ($users as $user) {
      $this->assertFalse($user->isBlocked());
    }

    // Set access time below the expiry threshold.
    $users['2']->setLastAccessTime(strtotime('-1 months'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();
    // Set access time over the expiry threshold.
    $users['3']->setLastAccessTime(strtotime('-7 months'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();
    // Set changed time below expiry threshold to make sure
    // users have some leeway.
    $users['5']->setLastAccessTime(strtotime('-1 months'))
      ->setChangedTime(strtotime('now'))
      ->save();

    $this->getSut()->cancelExpiredUsers();

    // Uid 3 should be the only expired user.
    $this->assertExpiredUsers([3], $users);

    // Set created time over the expiry threshold.
    $users['4']->set('created', strtotime('-7 months'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();

    // Only users 3 and 4 should be blocked.
    $this->getSut()->cancelExpiredUsers();
    $this->assertExpiredUsers([3, 4], $users);

    // Set access time over the delete threshold.
    User::load(3)->setLastAccessTime(strtotime('-5 years 1 day'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();
    // Set created time over the delete threshold.
    User::load(4)->set('created', strtotime('-5 years 1 day'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();

    $this->getSut()->deleteExpiredUsers();

    // Users 3 and 4 should be deleted now.
    $this->assertNull(User::load(3));
    $this->assertNull(User::load(4));
  }

  /**
   * Make sure content is reassigned when we delete the user.
   */
  public function testContentReassign() : void {
    // Make sure an UID 1 user exists.
    $this->createUser();

    $user = $this->createUser();
    $user->setLastAccessTime(strtotime('-5 years 1 day'))
      ->setChangedTime(strtotime('-2 days'))
      ->save();
    $this->assertTrue($user->id() > 1);

    $nodeIds = [];

    // Create multiple nodes and assign them to the newly created
    // user to make sure the content gets reassigned when
    // the account is deleted.
    for ($i = 0; $i < 15; $i++) {
      $node = $this->createNode([
        'type' => 'page',
        'uid' => $user->id(),
      ]);
      $this->assertEquals($user->id(), $node->getOwnerId());

      $nodeIds[] = $node->id();
    }

    $this->getSut()->deleteExpiredUsers();
    $this->assertNull(User::load($user->id()));

    foreach ($nodeIds as $nid) {
      $node = Node::load($nid);
      $this->assertEquals(0, $node->getOwnerId());
    }
  }

}

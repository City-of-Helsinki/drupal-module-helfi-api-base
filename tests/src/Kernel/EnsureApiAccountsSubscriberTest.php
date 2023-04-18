<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Tests EnsureApiAccountSubscriber.
 *
 * @group helfi_api_base
 */
class EnsureApiAccountsSubscriberTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('action');

    $this->config('helfi_api_base.api_accounts')
      ->set('accounts', [
        [
          'username' => 'helfi-admin',
          'password' => '123',
          'roles' => ['debug_api'],
        ],
      ])
      ->save();
  }

  /**
   * Tests that existing API account's password is reset.
   *
   * @covers \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber::getSubscribedEvents
   * @covers \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber::onPostDeploy
   * @covers \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber::__construct
   */
  public function testPasswordReset(): void {
    /** @var \Drupal\Core\Password\PhpassHashedPassword $passwordHasher */
    $passwordHasher = $this->container->get('password');

    $this->assertFalse(user_load_by_name('helfi-admin'));
    // Make sure account is created if one does not exist yet.
    /** @var \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber $service */
    $service = $this->container->get('helfi_api_base.ensure_api_accounts_subscriber');
    $service->onPostDeploy(new Event());
    $account = user_load_by_name('helfi-admin');
    $this->assertTrue($account->hasRole('debug_api'));
    $this->assertTrue($passwordHasher->check('123', $account->getPassword()));

    Role::create(['id' => 'test', 'label' => 'Test'])->save();
    Role::create(['id' => 'test2', 'label' => 'Test2'])->save();
    // Make sure we can change the password and add roles.
    $this->config('helfi_api_base.api_accounts')
      ->set('accounts', [
        [
          'username' => 'helfi-admin',
          'password' => '666',
          'roles' => ['test', 'test2'],
        ],
      ])
      ->save();
    $service->onPostDeploy(new Event());
    $account = user_load_by_name('helfi-admin');
    $this->assertTrue($account->hasRole('test'));
    $this->assertFalse($account->isBlocked());
    $this->assertTrue($account->hasRole('test2'));
    $this->assertTrue($passwordHasher->check('666', $account->getPassword()));
  }

}

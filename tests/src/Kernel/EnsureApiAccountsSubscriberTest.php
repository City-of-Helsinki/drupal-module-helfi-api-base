<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel\Plugin\DebugDataItem;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
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
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->installEntitySchema('user');
  }

  /**
   * Tests that existing API account's password is reset.
   *
   * @covers \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber::getSubscribedEvents
   * @covers \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber::onPostDeploy
   * @covers \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber::__construct
   */
  public function testPasswordReset(): void {
    $passwordHasher = $this->container->get('password');
    $this->assertFalse(user_load_by_name('helfi-admin'));
    // Make sure account is created if one does not exist yet.
    putenv('DRUPAL_NAVIGATION_API_KEY=' . base64_encode('helfi-admin:123'));
    /** @var \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber $service */
    $service = $this->container->get('helfi_api_base.ensure_api_accounts_subscriber');
    $service->onPostDeploy(new Event());

    /** @var \Drupal\Core\Password\PhpassHashedPassword $passwordHasher */
    $this->assertTrue($passwordHasher->check('123', user_load_by_name('helfi-admin')->getPassword()));

    // Make sure we can change the password.
    putenv('DRUPAL_NAVIGATION_API_KEY=' . base64_encode('helfi-admin:666'));
    $service->onPostDeploy(new Event());
    /** @var \Drupal\Core\Password\PhpassHashedPassword $passwordHasher */
    $this->assertTrue($passwordHasher->check('666', user_load_by_name('helfi-admin')->getPassword()));
  }

}

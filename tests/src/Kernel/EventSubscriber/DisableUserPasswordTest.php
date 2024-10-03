<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\EventSubscriber;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\helfi_api_base\Event\PostDeployEvent;
use Drupal\helfi_api_base\Features\FeatureManager;
use Drupal\user\Entity\User;

/**
 * Tests Rotate uid1 password.
 *
 * @group helfi_api_base
 */
class DisableUserPasswordTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'migrate',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('action');
    $this->installConfig('helfi_api_base');
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) : void {
    parent::register($container);

    $container->setParameter('helfi_api_base.disable_password_users', [
      'helfi-admin',
      1,
      'test@example.com',
    ]);
  }

  /**
   * Triggers the post deploy event.
   */
  private function triggerEvent() : void {
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $service */
    $service = $this->container->get('event_dispatcher');
    $service->dispatch(new PostDeployEvent());
  }

  /**
   * The feature should be enabled by default.
   */
  public function testFeatureIsEnabledByDefault() : void {
    /** @var \Drupal\helfi_api_base\Features\FeatureManager $service */
    $service = $this->container->get(FeatureManager::class);
    $this->assertTrue($service->isEnabled(FeatureManager::DISABLE_USER_PASSWORD));
  }

  /**
   * Make sure the password is not changed if the feature is not enabled.
   */
  public function testWithFeatureDisabled(): void {
    /** @var \Drupal\helfi_api_base\Features\FeatureManager $service */
    $service = $this->container->get(FeatureManager::class);
    $service->disableFeature(FeatureManager::DISABLE_USER_PASSWORD);
    $this->assertFalse($service->isEnabled(FeatureManager::DISABLE_USER_PASSWORD));

    $this->createUser([]);
    $currentPass = User::load(1)->getPassword();
    $this->assertNotEmpty($currentPass);
    $this->triggerEvent();
    $this->assertEquals($currentPass, User::load(1)->getPassword());
  }

  /**
   * Make sure the password is set to null.
   */
  public function testChangePassword() : void {
    /** @var \Drupal\helfi_api_base\Features\FeatureManager $service */
    $service = $this->container->get(FeatureManager::class);
    $service->enableFeature(FeatureManager::DISABLE_USER_PASSWORD);

    // Tests uid condition.
    $this->createUser([]);
    // Tests username condition.
    $account2 = $this->createUser(name: 'helfi-admin');
    // Tests mail condition.
    $account3 = $this->createUser(values: ['mail' => 'test@example.com']);
    $account4 = $this->createUser();

    $expectedUsers = [1, $account2->id(), $account3->id()];

    // Make sure the user has a password set.
    foreach ($expectedUsers as $uid) {
      $currentPass = User::load($uid)->getPassword();
      $this->assertNotEmpty($currentPass);
    }
    // Trigger the event and make sure all blacklisted users' password
    // is set to null.
    $this->triggerEvent();

    foreach ($expectedUsers as $uid) {
      $this->assertNull(User::load($uid)->getPassword());
    }
    $this->assertNull(User::load($uid)->getPassword());
    // Make sure only blacklisted users are affected.
    $this->assertNotNull(User::load($account4->id())->getPassword());
  }

}

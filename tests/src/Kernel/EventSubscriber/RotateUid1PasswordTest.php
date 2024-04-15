<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\EventSubscriber;

use Drupal\helfi_api_base\Event\PostDeployEvent;
use Drupal\helfi_api_base\Features\FeatureManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;

/**
 * Tests Rotate uid1 password.
 *
 * @group helfi_api_base
 */
class RotateUid1PasswordTest extends KernelTestBase {

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
    $service = $this->container->get(FeatureManagerInterface::class);
    $this->assertTrue($service->isEnabled(FeatureManagerInterface::ROTATE_UID1_PASSWORD));
  }

  /**
   * Make sure the password is not changed if the feature is not enabled.
   */
  public function testWithFeatureDisabled(): void {
    /** @var \Drupal\helfi_api_base\Features\FeatureManager $service */
    $service = $this->container->get(FeatureManagerInterface::class);
    $service->disableFeature(FeatureManagerInterface::ROTATE_UID1_PASSWORD);
    $this->assertFalse($service->isEnabled(FeatureManagerInterface::ROTATE_UID1_PASSWORD));

    $this->createUser([]);
    $currentPass = User::load(1)->getPassword();
    $this->assertNotEmpty($currentPass);
    $this->triggerEvent();
    $this->assertEquals($currentPass, User::load(1)->getPassword());
  }

  /**
   * Test missing account.
   */
  public function testAccountNotFound() : void {
    /** @var \Drupal\helfi_api_base\Features\FeatureManager $service */
    $service = $this->container->get(FeatureManagerInterface::class);
    $this->assertTrue($service->isEnabled(FeatureManagerInterface::ROTATE_UID1_PASSWORD));
    $this->assertEmpty(User::load(1));
    $this->triggerEvent();
  }

  /**
   * Make sure the password is changed.
   */
  public function testChangePassword() : void {
    /** @var \Drupal\helfi_api_base\Features\FeatureManager $service */
    $service = $this->container->get(FeatureManagerInterface::class);
    $service->enableFeature(FeatureManagerInterface::ROTATE_UID1_PASSWORD);

    $this->createUser([]);
    $currentPass = User::load(1)->getPassword();
    $this->assertNotEmpty($currentPass);
    $this->triggerEvent();
    $this->assertNotEquals($currentPass, User::load(1)->getPassword());
  }

}

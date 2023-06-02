<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel\EventSubscriber;

use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Tests EnsureApiAccountSubscriber.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\EventSubscriber\EnsureApiAccountsSubscriber
 *
 * @group helfi_api_base
 */
class EnsureApiAccountsSubscriberTest extends KernelTestBase {

  use UserCreationTrait;
  use EnvironmentResolverTrait;

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
  protected function setUp() : void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('action');
    $this->setActiveProject(Project::ASUMINEN, EnvironmentEnum::Test);

    $this->config('helfi_api_base.api_accounts')
      ->set('accounts', [
        [
          'username' => 'helfi-admin',
          'password' => '123',
          'roles' => ['debug_api'],
        ],
      ])
      ->save();
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
    $moduleHandler = $this->container->get('module_handler');
    $moduleHandler->loadInclude('helfi_api_base', 'install');
    // Make sure install hook is run.
    helfi_api_base_install();
  }

  /**
   * Tests that existing API account's password is reset.
   *
   * @covers ::getSubscribedEvents
   * @covers ::onPostDeploy
   * @covers ::__construct
   */
  public function testPasswordReset(): void {
    /** @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = $this->container->get('messenger');
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
          'roles' => ['test', 'test2', 'test3'],
        ],
      ])
      ->save();
    $service->onPostDeploy(new Event());
    $account = user_load_by_name('helfi-admin');
    $this->assertEquals('drupal+helfi-admin@hel.fi', $account->getEmail());
    $this->assertTrue($account->hasRole('test'));
    $this->assertFalse($account->isBlocked());
    $this->assertTrue($account->hasRole('test2'));
    $this->assertFalse($account->hasRole('test3'));
    $this->assertTrue($passwordHasher->check('666', $account->getPassword()));

    $messages = $messenger->messagesByType('status');
    $this->assertCount(1, $messages);

    $found = array_filter($messages, function (string $message) {
      return $message === '[helfi_api_base]: Account helfi-admin not found. Creating a new account.';
    });
    $this->assertCount(1, $found);

    $messages = $messenger->messagesByType('error');
    $this->assertCount(1, $messages);
    $found = array_filter($messenger->messagesByType('error'), function (string $message) {
      return $message === 'Role test3 not found. Skipping.';
    });
    $this->assertCount(1, $found);
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests user login subscriber.
 *
 * @group helfi_api_base
 */
class UserLoginSubscriberTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * Drupal request stack.
   */
  private RequestStack $requestStack;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');

    // Store requestStack before tests so
    // that it can be restored in tearDown().
    $this->requestStack = $this->container->get('request_stack');
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    $this->container->set('request_stack', $this->requestStack);

    parent::tearDown();
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) : void {
    parent::register($container);

    $container->setParameter('helfi_api_base.restricted_roles', [
      'test_role',
    ]);
  }

  /**
   * Tests login restrictions.
   */
  public function testLoginRestrictions() : void {
    $requestStack = $this->prophesize(RequestStack::class);
    $requestStack
      ->getCurrentRequest()
      ->willReturn(new Request(server: [
        'REMOTE_ADDR' => '123.123.123.123',
      ]));

    $this->container->set('request_stack', $requestStack->reveal());

    $user1 = $this->createUser();
    $user2 = $this->createUser();

    // Having this role should prevent login from non-private IP ranges.
    $this->createRole([], name: 'test_role');
    $user2->addRole('test_role');

    // Login attempt from non-restricted user.
    $this->setCurrentUser($user1);

    // Verify that logging in succeeded.
    $current = $this->container->get('current_user');
    $this->assertInstanceOf(AccountProxyInterface::class, $current);
    $this->assertFalse($current->isAnonymous());

    // Login attempt from restricted user.
    $this->setCurrentUser($user2);

    // Verify that logging in fails.
    $current = $this->container->get('current_user');
    $this->assertInstanceOf(AccountProxyInterface::class, $current);
    $this->assertTrue($current->isAnonymous());
  }

  /**
   * Tests login from private network.
   */
  public function testLoginRestrictionsFromPrivateNetwork() : void {
    $requestStack = $this->prophesize(RequestStack::class);
    $requestStack
      ->getCurrentRequest()
      ->willReturn(new Request(server: [
        'REMOTE_ADDR' => '192.168.1.2',
      ]));

    $this->container->set('request_stack', $requestStack->reveal());

    $user = $this->createUser();

    // Having this role should prevent login from non-private IP ranges.
    $this->createRole([], name: 'test_role');
    $user->addRole('test_role');

    // Login attempt from non-restricted user.
    $this->setCurrentUser($user);

    // Verify that logging in succeeded.
    $current = $this->container->get('current_user');
    $this->assertInstanceOf(AccountProxyInterface::class, $current);
    $this->assertFalse($current->isAnonymous());
  }

}

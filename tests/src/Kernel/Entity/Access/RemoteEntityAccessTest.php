<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;

/**
 * Tests remote entity access.
 *
 * @group helfi_api_base
 */
class RemoteEntityAccessTest extends ApiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'remote_entity_test',
    'menu_link_content',
    'user',
  ];

  /**
   * The remote entity to test.
   *
   * @var \Drupal\remote_entity_test\Entity\RemoteEntityTest|null
   */
  private ?RemoteEntityTest $rmt = NULL;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('remote_entity_test');
    // Create a dummy user before tests to make sure our actual user is not
    // UID1 and getting all permissions automatically.
    $this->drupalCreateUser();

    $this->rmt = RemoteEntityTest::create([
      'id' => 1,
      'title' => 'Test 1',
    ]);
    $this->rmt->save();
  }

  /**
   * Asserts remote entity access.
   *
   * @param array $ops
   *   The ops [operation => operation is allowed(bool) ].
   * @param \Drupal\remote_entity_test\Entity\RemoteEntityTest $entity
   *   The entity.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   */
  private function assertRemoteEntityAccess(array $ops, RemoteEntityTest $entity, AccountInterface $account) {
    foreach ($ops as $op => $allowed) {
      $access = $entity->access($op, $account, TRUE);
      $this->assertEquals($allowed, $access->isAllowed());
    }
  }

  /**
   * Tests permissions without account.
   */
  public function testAnonymous() : void {
    $actions = [
      'view' => "The following permissions are required: 'view remote entities' OR 'administer remote entities'.",
      'update' => "The following permissions are required: 'edit remote entities' OR 'administer remote entities'.",
      'delete' => "The following permissions are required: 'delete remote entities' OR 'administer remote entities'.",
      'create' => "The following permissions are required: 'create remote entities' OR 'administer remote entities'.",
    ];

    foreach ($actions as $action => $message) {
      $access = $this->rmt->access($action, return_as_object: TRUE);
      $this->assertFalse($access->isAllowed());
      $this->assertEquals($message, $access->getReason());
    }
  }

  /**
   * Tests 'administer remote entities' permission.
   */
  public function testAdminAccess() : void {
    // Make sure user with administer remote entities gets access
    // to everything.
    $account = $this->drupalCreateUser([
      'administer remote entities',
    ]);

    $this->assertRemoteEntityAccess([
      'view' => TRUE,
      'update' => TRUE,
      'delete' => TRUE,
      'create' => TRUE,
    ], $this->rmt, $account);
  }

  /**
   * Tests individual entity permissions.
   */
  public function testIndividualPermissions() : void {
    $permissions = [
      'view remote entities' => [
        'view' => TRUE,
        'update' => FALSE,
        'delete' => FALSE,
        'create' => FALSE,
      ],
      'edit remote entities' => [
        'view' => FALSE,
        'update' => TRUE,
        'delete' => FALSE,
        'create' => FALSE,
      ],
      'delete remote entities' => [
        'view' => FALSE,
        'update' => FALSE,
        'delete' => TRUE,
        'create' => FALSE,
      ],
      'create remote entities' => [
        'view' => FALSE,
        'update' => FALSE,
        'delete' => FALSE,
        'create' => TRUE,
      ],
    ];

    foreach ($permissions as $permission => $ops) {
      $account = $this->drupalCreateUser([
        $permission,
      ]);
      $this->assertRemoteEntityAccess($ops, $this->rmt, $account);
    }
  }

}

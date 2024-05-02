<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Utility;

use Drupal\helfi_api_base\Entity\Utility\UserEntitySanitizer;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the UserEntitySanitizer class.
 *
 * @group helfi_api_base
 * @coversDefaultClass \Drupal\helfi_api_base\Entity\Utility\UserEntitySanitizer
 */
class UserEntitySanitizerTest extends ApiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'system',
  ];

  /**
   * User entity sanitizer.
   */
  private UserEntitySanitizer $sanitizer;

  /**
   * Test account.
   *
   * @var \Drupal\user\Entity\User
   */
  private User $testUser;

  /**
   * Default values for the test user.
   *
   * @var array|string[]
   */
  private array $defaultValues = [
    'username' => 'Test',
    'password' => 'test',
    'email' => 'lTqgB@drupal.hel.ninja',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();
    $this->sanitizer = $this->container->get('helfi_api_base.user_entity_sanitizer');

    // Create a test user with default values.
    $this->testUser = $this->createUser(
      ['access content'],
      $this->defaultValues['username'],
      FALSE,
      [
        'pass' => $this->defaultValues['password'],
        'mail' => $this->defaultValues['email'],
      ],
    );

    // Initially set testUser blocked.
    $this->testUser->block()->save();
  }

  /**
   * Test active user sanitation.
   */
  public function testActiveUserSanitation(): void {
    // Activate testUser.
    $this->testUser->activate()->save();
    $operation = $this->sanitizer->sanitizeUserEntity($this->testUser, []);

    // Test that the operation returns 0 as nothing should happen.
    $this->assertEquals($operation, 0);

    // Assert that the user's information remains unchanged.
    $this->assertEquals($this->testUser->getAccountName() === $this->defaultValues['username'], TRUE);
    $this->assertEquals($this->testUser->getEmail() === $this->defaultValues['email'], TRUE);
    $password_service = $this->container->get('password');
    $this->assertEquals($password_service->check($this->defaultValues['password'], $this->testUser->getPassword()), TRUE);

    // Test user sanitation with user id.
    $operation = $this->sanitizer->sanitizeUserEntity((int) $this->testUser->id(), []);
    // Test that the operation returns 0 as nothing should happen.
    $this->assertEquals($operation, 0);
  }

  /**
   * Test the sanitization of the test user account name.
   */
  public function testUserNameSanitation(): void {
    // Sanitize user name.
    $operation = $this->sanitizer->sanitizeUserEntity(
      $this->testUser,
      ['username' => TRUE]
    );

    // Test that the operation other than 0 as user information is sanitized.
    $this->assertNotEquals($operation, 0);

    // Assert that the user account name has been sanitized.
    $this->assertNotEquals($this->testUser->getAccountName() === $this->defaultValues['username'], TRUE);

    // Check all fields are still the same (have not been sanitized).
    $this->assertEquals($this->testUser->getEmail() === $this->defaultValues['email'], TRUE);
    $password_service = $this->container->get('password');
    $this->assertEquals($password_service->check($this->defaultValues['password'], $this->testUser->getPassword()), TRUE);
  }

  /**
   * Test the sanitization of the test user password.
   */
  public function testUserPasswordSanitation(): void {
    // Sanitize user name.
    $operation = $this->sanitizer->sanitizeUserEntity(
      $this->testUser,
      ['password' => TRUE]
    );

    // Test that the operation other than 0 as user information is sanitized.
    $this->assertNotEquals($operation, 0);

    // Assert that the user account password has been sanitized.
    $password_service = $this->container->get('password');
    $this->assertNotEquals($password_service->check($this->defaultValues['password'], $this->testUser->getPassword()), TRUE);

    // Check all fields are still the same (have not been sanitized).
    $this->assertEquals($this->testUser->getEmail() === $this->defaultValues['email'], TRUE);
    $this->assertEquals($this->testUser->getAccountName() === $this->defaultValues['username'], TRUE);
  }

  /**
   * Test the sanitization of the test user email.
   */
  public function testUserEmailSanitation(): void {
    // Sanitize user name.
    $operation = $this->sanitizer
      ->sanitizeUserEntity($this->testUser, ['email' => TRUE]);

    // Test that the operation other than 0 as user information is sanitized.
    $this->assertNotEquals($operation, 0);

    // Assert that the user account email has been sanitized.
    $this->assertNotEquals($this->testUser->getEmail() === $this->defaultValues['email'], TRUE);

    // Check all other fields are still the same (have not been sanitized).
    $this->assertEquals($this->testUser->getAccountName() === $this->defaultValues['username'], TRUE);
    $password_service = $this->container->get('password');
    $this->assertEquals($password_service->check($this->defaultValues['password'], $this->testUser->getPassword()), TRUE);
  }

  /**
   * Test the sanitization of all user information.
   */
  public function testUserFieldsSanitation(): void {
    // Sanitize user name.
    $operation = $this->sanitizer->sanitizeUserEntity(
      $this->testUser,
      [
        'username' => TRUE,
        'password' => TRUE,
        'email' => TRUE,
      ]
    );

    // Test that the operation other than 0 as user information is sanitized.
    $this->assertNotEquals($operation, 0);

    // Assert that the all fields have been sanitized.
    $this->assertNotEquals($this->testUser->getAccountName() === $this->defaultValues['username'], TRUE);
    $this->assertNotEquals($this->testUser->getEmail() === $this->defaultValues['email'], TRUE);
    $password_service = $this->container->get('password');
    $this->assertNotEquals($password_service->check($this->defaultValues['password'], $this->testUser->getPassword()), TRUE);
  }

}

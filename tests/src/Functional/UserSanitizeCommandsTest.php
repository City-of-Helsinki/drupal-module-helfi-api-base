<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Commands\UserSanitizeCommands;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Drupal\user\Entity\User;
use Prophecy\PhpUnit\ProphecyTrait;
use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;


/**
 * Tests UserSanitize command.
 *
 * @group helfi_api_base
 * @coversDefaultClass \Drupal\helfi_api_base\Commands\UserSanitizeCommands
 */
class UserSanitizeCommandsTest extends BrowserTestBase {

  use DrushTestTrait;

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
  protected $defaultTheme = 'stark';

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
   * User account for testing the sanitation form.
   *
   * @var \Drupal\user\Entity\User
   */
  protected User $testUser;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->testUser = $this->drupalCreateUser([], 'testUser');
  }

  /**
   * Tests helfi:yser-sanitize command.
   */
  public function testUserSanitizeCommandWithFields() {
    $this->drush('helfi:user-sanitize', [
      'uid' => $this->testUser->id(),
      'fields' => 'email,username'
    ]);

    $this->assertEquals($this->testUser->getAccountName() === $this->defaultValues['username'], FALSE);
    $this->assertEquals($this->testUser->getEmail() === $this->defaultValues['email'], TRUE);
    $password_service = $this->container->get('password');
    $this->assertEquals($password_service->check($this->defaultValues['password'], $this->testUser->getPassword()), TRUE);
  }

  /**
   * Tests helfi:yser-sanitize command.
   */
  public function testUserSanitizeCommandWithOutFields() {
    $this->drush('helfi:user-sanitize', [
      'uid' => $this->testUser->id(),
    ]);
    $this->assertEquals($this->testUser->getAccountName() === $this->defaultValues['username'], FALSE);
    $this->assertEquals($this->testUser->getEmail() === $this->defaultValues['email'], FALSE);
    $password_service = $this->container->get('password');
    $this->assertEquals($password_service->check($this->defaultValues['password'], $this->testUser->getPassword()), FALSE);
  }

}

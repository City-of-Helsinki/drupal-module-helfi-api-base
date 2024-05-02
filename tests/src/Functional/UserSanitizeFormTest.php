<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Tests user sanitation form and drush command.
 *
 * @group helfi_api_base
 * @covers \Drupal\helfi_api_base\Entity\Form\UserEntitySanitizeForm
 */
class UserSanitizeFormTest extends BrowserTestBase {

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
   * Admin user account for testing the sanitation form.
   *
   * @var \Drupal\user\Entity\User
   */
  protected User $adminUser;

  /**
   * User account for testing the sanitation form.
   *
   * @var \Drupal\user\Entity\User
   */
  protected User $testUser;

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
  public function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'access user profiles',
      'administer users',
      'cancel account',
      'delete user accounts',
      'sanitize user accounts',
    ], 'adminUser');

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
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests admin/people sanitize user account operation.
   */
  public function testUserSanitizeOperation(): void {
    // Make sure the sanitize link is available only for blocked users.
    $this->drupalGet('/admin/people');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefNotExists("/user/{$this->testUser->id()}/sanitize");

    // Deactivate testUser to reveal the sanitize link and click it.
    $this->testUser->block()->save();
    $this->drupalGet('/admin/people');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists("/user/{$this->testUser->id()}/sanitize");

    // Test that the sanitize link works.
    $this->getSession()->getPage()->find('xpath', '//a[contains(@href, "/user/' . $this->testUser->id() . '/sanitize")]')->click();
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests user cancel method form.
   */
  public function testUserSanitizeFormWithoutCsrfToken(): void {
    $this->drupalGet("user/{$this->testUser->id()}/sanitize");
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests user sanitation form.
   */
  public function testUserSanitizeForm(): void {
    // Test that the sanitize link works.
    $this->testUser->block()->save();
    $this->drupalGet('/admin/people');
    $this->assertSession()->statusCodeEquals(200);
    $this->getSession()
      ->getPage()
      ->find('xpath', '//a[contains(@href, "/user/' . $this->testUser->id() . '/sanitize")]')
      ->click();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->testUser->getAccountName());
    $this->assertSession()->buttonExists('Sanitize');

    // Test the form functionality without selecting any fields.
    $this->submitForm([], 'Sanitize');
    $this->assertSession()->pageTextContains('I understand that this action will sanitize all selected data from the user account and the action cannot be undone. field is required.');
    $this->submitForm(['confirm' => 'on'], 'Sanitize');
    $this->assertSession()->pageTextContains('There was an error with saving the sanitized information to the account.');

    // Test the form functionality with selecting some fields.
    $this->submitForm(['confirm' => 'on', 'fields[email]' => TRUE], 'Sanitize');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('People');
    $this->assertSession()->pageTextContains("User account id {$this->testUser->id()} was sanitized.");
  }

}

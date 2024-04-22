<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

/**
 * Tests user cancel method form.
 *
 * @group helfi_api_base
 */
class UserCancelFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests user cancel method form.
   */
  public function testUserCancelForm(): void {
    // Create user accounts for testing the delete methods.
    $superAdminUser = $this->drupalCreateUser([], 'superAdminUser', TRUE);
    $adminUser = $this->drupalCreateUser([
      'delete user accounts',
      'access user profiles',
      'administer users',
      'cancel account',
    ], 'superUser');
    $editorUser = $this->drupalCreateUser([
      'access user profiles',
      'cancel account',
    ], 'editorUser');
    $testUser = $this->drupalCreateUser([], 'testUser');

    // Test that the superAdminUser can see all cancellation methods
    // for the testUser account.
    $this->drupalLogin($superAdminUser);
    $this->drupalGet('/user/' . $testUser->id() . '/cancel');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('xpath', '//input[@value="user_cancel_block"]');
    $this->assertSession()->elementExists('xpath', '//input[@value="user_cancel_block_unpublish"]');
    $this->assertSession()->elementExists('xpath', '//input[@value="user_cancel_reassign"]');
    $this->assertSession()->elementExists('xpath', '//input[@value="user_cancel_delete"]');

    // Test that the adminUser can see all cancellation methods
    // for the testUser account.
    $this->drupalLogin($adminUser);
    $this->drupalGet('/user/' . $testUser->id() . '/cancel');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('xpath', '//input[@value="user_cancel_block"]');
    $this->assertSession()->elementExists('xpath', '//input[@value="user_cancel_block_unpublish"]');
    $this->assertSession()->elementExists('xpath', '//input[@value="user_cancel_reassign"]');
    $this->assertSession()->elementExists('xpath', '//input[@value="user_cancel_delete"]');

    // Test that the editorUser cannot access the cancel page.
    $this->drupalLogin($editorUser);
    $this->drupalGet('/user/' . $testUser->id() . '/cancel');
    $this->assertSession()->statusCodeEquals(403);
  }

}

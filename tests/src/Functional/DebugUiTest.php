<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

/**
 * Tests debug data rest resource.
 *
 * @group helfi_api_base
 */
class DebugUiTest extends MigrationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'rest',
    'remote_entity_test',
    'composer_lock_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the admin UI route.
   */
  public function testDebugAdminUi() : void {
    $this->drupalGet('/admin/debug');
    $this->assertSession()->statusCodeEquals(403);

    $account = $this->createUser(['access debug page']);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/debug');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Composer');
    $this->assertSession()->pageTextContains('Migrate');
  }

}

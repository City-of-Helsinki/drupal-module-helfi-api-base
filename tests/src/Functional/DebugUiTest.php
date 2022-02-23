<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\user\Entity\Role;

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
   * Tests /debug endpoint.
   */
  public function testDebugEndpoint() : void {
    $this->drupalGet('/api/v1/debug');
    $this->assertSession()->statusCodeEquals(403);

    // Allow all users to fetch resources.
    Role::load('anonymous')
      ->grantPermission('restful get helfi_debug_data')
      ->save();

    $this->drupalGet('/api/v1/debug');
    $this->assertSession()->statusCodeEquals(200);
    $content = json_decode($this->getSession()->getPage()->getContent(), TRUE);

    $this->assertNotEmpty($content['composer']);
    // Make sure we have no imported items by default.
    $this->assertEquals(0, $content['migrate']['data'][0]['imported']);
    // Run migration to verify that caches are cleared properly.
    $this->executeMigration('dummy_migrate');

    $this->drupalGet('/api/v1/debug');
    $this->assertSession()->statusCodeEquals(200);
    $content = json_decode($this->getSession()->getPage()->getContent(), TRUE);
    $this->assertEquals(4, $content['migrate']['data'][0]['imported']);
  }

  /**
   * Tests the admin UI route.
   */
  public function testDebugAdminUi() : void {
    $this->drupalGet('/admin/debug');
    $this->assertSession()->statusCodeEquals(403);

    $account = $this->createUser(['restful get helfi_debug_data']);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/debug');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Composer');
    $this->assertSession()->pageTextContains('Migrate');
  }

}

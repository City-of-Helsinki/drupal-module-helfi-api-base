<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests debug data rest resource.
 *
 * @group helfi_api_base
 */
class DebugUiTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'rest',
    'helfi_api_base',
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
  }

}

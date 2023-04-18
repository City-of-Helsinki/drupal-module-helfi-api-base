<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Core\Config\Schema\SchemaIncompleteException;
use Drupal\Tests\BrowserTestBase as CoreBrowserTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests helfi_api_base install hooks.
 *
 * @group helfi_api_base
 */
class InstallTest extends CoreBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    try {
      // We override 'api_accounts' config in platform's settings.php and the
      // 'helfi_api_base' module is not installed at this point, so the
      // schema does not exist yet and will fail. Catch the exception to
      // simulate the normal installation process.
      $this->config('helfi_api_base.api_accounts')
        ->set('accounts', [
          [
            'username' => 'helfi-admin',
            'password' => '123',
            'roles' => ['debug_api'],
          ],
        ])
        ->save();
    }
    catch (SchemaIncompleteException) {
    }
  }

  /**
   * Make sure debug api role is created when accounts are defined.
   */
  public function testInstall() : void {
    // Enable the 'helfi_api_base' module to trigger hook_modules_installed().
    $this->container->get('module_installer')->install(['helfi_api_base']);
    // Make sure debug api role is created when we have api accounts with
    // 'debug_api' roles.
    $this->assertFalse(Role::load('debug_api')->hasPermission('restful get helfi_debug_data'));

    // Make sure required 'rest' permissions are granted when the 'rest' module
    // is enabled.
    $this->container->get('module_installer')->install(['rest']);
    $this->assertTrue(Role::load('debug_api')->hasPermission('restful get helfi_debug_data'));
  }

}

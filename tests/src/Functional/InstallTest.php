<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Core\Config\Schema\SchemaIncompleteException;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\Tests\BrowserTestBase as CoreBrowserTestBase;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Drupal\user\Entity\Role;

/**
 * Tests helfi_api_base install hooks.
 *
 * @group helfi_api_base
 */
class InstallTest extends CoreBrowserTestBase {

  use EnvironmentResolverTrait;

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
    /** @var \Drupal\Core\Extension\ModuleInstaller $moduleInstaller */
    $moduleInstaller = $this->container->get('module_installer');
    // Enable the 'helfi_api_base' module to trigger hook_install().
    $moduleInstaller->install(['helfi_api_base']);
    // Make sure 'debug_api' role is not created when active project is not
    // defined.
    $this->assertNull(Role::load('debug_api'));

    // Re-install the module to make sure 'debug_api' role is created and
    // required permissions are granted when active environment is defined.
    $moduleInstaller->uninstall(['helfi_api_base']);

    try {
      $this->setActiveProject(Project::ASUMINEN, EnvironmentEnum::Local);
    }
    catch (SchemaIncompleteException) {
    }
    $moduleInstaller->install(['helfi_api_base']);

    $this->assertTrue(Role::load('debug_api')->hasPermission('restful get helfi_debug_data'));
  }

}

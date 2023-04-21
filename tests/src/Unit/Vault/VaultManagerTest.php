<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\Vault;

use Drupal\helfi_api_base\Vault\AuthorizationToken;
use Drupal\helfi_api_base\Vault\VaultManager;
use Drupal\helfi_api_base\Vault\VaultManagerFactory;
use Drupal\Tests\UnitTestCase;

/**
 * Tests Vault manager.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Vault\VaultManager
 * @group helfi_api_base
 */
class VaultManagerTest extends UnitTestCase {

  /**
   * @covers ::__construct
   */
  public function testInvalidVaultItem() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/Expected an instance of /');
    new VaultManager(['string']);
  }

  /**
   * @covers \Drupal\helfi_api_base\Vault\VaultManagerFactory::create
   * @covers \Drupal\helfi_api_base\Vault\VaultManagerFactory::__construct
   * @dataProvider factoryExceptionData
   */
  public function testFactoryException(array $vault) : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectErrorMessageMatches('/Missing required/');
    $sut = new VaultManagerFactory($this->getConfigFactoryStub([
      'helfi_api_base.api_accounts' => [
        'vault' => $vault,
      ],
    ]));
    $sut->create();
  }

  /**
   * Data provider for testFactoryException().
   *
   * @return array[]
   *   The data.
   */
  public function factoryExceptionData() : array {
    return [
      [
        [
          ['plugin' => '123', 'id' => 'test'],
        ],
      ],
      [
        [
          ['plugin' => '123', 'data' => ''],
        ],
      ],
      [
        [
          ['id' => '123', 'data' => ''],
        ],
      ],
    ];
  }

  /**
   * @covers ::__construct
   * @covers ::get
   * @covers \Drupal\helfi_api_base\Vault\AuthorizationToken::__construct
   * @covers \Drupal\helfi_api_base\Vault\AuthorizationToken::id
   * @covers \Drupal\helfi_api_base\Vault\AuthorizationToken::data
   * @covers \Drupal\helfi_api_base\Vault\VaultManagerFactory::create
   * @covers \Drupal\helfi_api_base\Vault\VaultManagerFactory::__construct
   */
  public function testFactory() : void {
    $sut = new VaultManagerFactory($this->getConfigFactoryStub([
      'helfi_api_base.api_accounts' => [
        'vault' => [
          [
            'plugin' => AuthorizationToken::PLUGIN,
            'id' => 'test_local',
            'data' => 'token',
          ],
        ],
      ],
    ]));
    $instance = $sut->create();
    $this->assertInstanceOf(VaultManager::class, $instance);
    $this->assertInstanceOf(AuthorizationToken::class, $instance->get('test_local'));
  }

  /**
   * @covers \Drupal\helfi_api_base\Vault\AuthorizationToken::__construct
   * @covers \Drupal\helfi_api_base\Vault\AuthorizationToken::id
   * @covers \Drupal\helfi_api_base\Vault\AuthorizationToken::data
   */
  public function testAuthorizationToken() : void {
    $token = new AuthorizationToken('test', '123');
    $this->assertEquals('123', $token->data());
    $this->assertEquals('test', $token->id());
  }

}

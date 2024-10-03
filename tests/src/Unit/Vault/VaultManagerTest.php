<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Vault;

use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Vault\AuthorizationToken;
use Drupal\helfi_api_base\Vault\Json;
use Drupal\helfi_api_base\Vault\VaultManager;
use Drupal\helfi_api_base\Vault\VaultManagerFactory;

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
    $caught = FALSE;
    try {
      new VaultManager(['string']);
    }
    catch (\InvalidArgumentException $e) {
      $caught = TRUE;
      $this->assertMatchesRegularExpression('/Expected an instance of /', $e->getMessage());
    }
    $this->assertTrue($caught);
  }

  /**
   * @covers \Drupal\helfi_api_base\Vault\VaultManagerFactory::create
   * @covers \Drupal\helfi_api_base\Vault\VaultManagerFactory::__construct
   * @dataProvider factoryExceptionData
   */
  public function testFactoryException(array $vault) : void {
    $caught = FALSE;
    try {
      $sut = new VaultManagerFactory($this->getConfigFactoryStub([
        'helfi_api_base.api_accounts' => [
          'vault' => $vault,
        ],
      ]));
      $sut->create();
    }
    catch (\InvalidArgumentException $e) {
      $this->assertMatchesRegularExpression('/Missing required/', $e->getMessage());
      $caught = TRUE;
    }
    $this->assertTrue($caught);
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
    $accounts = [
      [
        'plugin' => AuthorizationToken::PLUGIN,
        'id' => 'test_local',
        'data' => 'token',
      ],
      [
        'plugin' => Json::PLUGIN,
        'id' => 'test_local2',
        'data' => json_encode(['value' => '123']),
      ],
    ];

    $sut = new VaultManagerFactory($this->getConfigFactoryStub([
      'helfi_api_base.api_accounts' => [
        'vault' => $accounts,
      ],
    ]));
    $instance = $sut->create();
    $this->assertInstanceOf(VaultManager::class, $instance);

    foreach ($accounts as $account) {
      $this->assertSame($account['id'], $instance->get($account['id'])->id());
      $this->assertSame($account['plugin'], $instance->get($account['id'])::PLUGIN);
    }
  }

  /**
   * @covers \Drupal\helfi_api_base\Vault\Json::__construct
   */
  public function testJsonException() : void {
    $this->expectException(\JsonException::class);
    new Json('test', '{');
  }

  /**
   * @covers \Drupal\helfi_api_base\Vault\Json::__construct
   * @covers \Drupal\helfi_api_base\Vault\Json::id
   * @covers \Drupal\helfi_api_base\Vault\Json::data
   */
  public function testJson() : void {
    $sut = new Json('test', json_encode([
      'endpoint' => '123',
      'access_key' => '321',
    ]));
    $this->assertSame('test', $sut->id());
    $this->assertSame('123', $sut->data()->endpoint);
    $this->assertSame('321', $sut->data()->access_key);
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

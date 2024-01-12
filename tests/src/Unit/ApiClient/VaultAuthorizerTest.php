<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\helfi_api_base\ApiClient\VaultAuthorizer;
use Drupal\helfi_api_base\Vault\AuthorizationToken;
use Drupal\helfi_api_base\Vault\VaultManager;
use Drupal\Tests\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\ApiClient\VaultAuthorizer
 * @group helfi_api_base
 */
class VaultAuthorizerTest extends UnitTestCase {

  const VAULT_MANAGER_KEY = 'test_vault';

  use ProphecyTrait;

  /**
   * @covers ::__construct
   * @covers ::getAuthorization
   * @covers ::getToken
   */
  public function testVaultAuthorization() : void {
    $vaultManager = new VaultManager([
      new AuthorizationToken(self::VAULT_MANAGER_KEY, '123'),
    ]);
    $sut = new VaultAuthorizer(
      $this->getConfigFactoryStub([]),
      $vaultManager,
      self::VAULT_MANAGER_KEY,
    );
    $this->assertEquals('Basic 123', $sut->getAuthorization());
  }

  /**
   * @covers ::__construct
   * @covers ::getAuthorization
   * @covers ::getToken
   */
  public function testEmptyAuthorization() : void {
    $sut = new VaultAuthorizer(
      $this->getConfigFactoryStub([]),
      new VaultManager([]),
      self::VAULT_MANAGER_KEY,
    );
    $this->assertNull($sut->getAuthorization());
  }

  /**
   * @covers ::__construct
   * @covers ::getAuthorization
   * @covers ::getToken
   */
  public function testFallbackConfigAuthorization() : void {
    $sut = new VaultAuthorizer(
      $this->getConfigFactoryStub(['test_config' => ['key' => '123']]),
      new VaultManager([]),
      self::VAULT_MANAGER_KEY,
      'test_config:key'

    );
    $this->assertEquals('Basic 123', $sut->getAuthorization());
  }

}

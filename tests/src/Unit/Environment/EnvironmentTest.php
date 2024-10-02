<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\helfi_api_base\Environment\Address;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Service;
use Drupal\helfi_api_base\Environment\ServiceEnum;
use Drupal\Tests\UnitTestCase;

/**
 * Tests environment value object.
 *
 * @group helfi_api_base
 */
class EnvironmentTest extends UnitTestCase {

  /**
   * Gets the SUT.
   *
   * @param array $paths
   *   The paths.
   * @param array $services
   *   The services.
   *
   * @return \Drupal\helfi_api_base\Environment\Environment
   *   The SUT.
   */
  private function getSut(array $paths = [], array $services = []) : Environment {
    return new Environment(
      new Address('www.hel.fi'),
      new Address('internal-address.local', 'http', 8080),
      $paths,
      EnvironmentEnum::Test,
      $services,
    );
  }

  /**
   * Tests getters.
   */
  public function testSimpleGetters() : void {
    $sut = $this->getSut();
    $this->assertEquals(EnvironmentEnum::Test, $sut->getEnvironment());
    $this->assertEquals(EnvironmentEnum::Test->value, $sut->getEnvironmentName());
    $this->assertEquals('https://www.hel.fi', $sut->getBaseUrl());
    $this->assertEquals('http://internal-address.local:8080', $sut->getInternalBaseUrl());
    $this->assertNull($sut->getService(ServiceEnum::ElasticProxy));
  }

  /**
   * Make sure only Service instances can be passed.
   */
  public function testServiceException() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->getSut(services: [1]);
  }

  /**
   * Tests service getter.
   */
  public function testGetService() : void {
    $sut = $this->getSut(
      services: [
        new Service(ServiceEnum::ElasticProxy, new Address('helfi-etusivu-elastic')),
      ],
    );
    $this->assertInstanceOf(Service::class, $sut->getService(ServiceEnum::ElasticProxy));
  }

  /**
   * Test path validation.
   */
  public function testGetUrlException() : void {
    $sut = $this->getSut(['fi' => 'test-path']);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Path not found for "en" language.');
    $sut->getUrl('en');
  }

  /**
   * Tests getUrl() method.
   */
  public function testGetUrl() : void {
    $sut = new Environment(
      new Address('www.hel.fi'),
      new Address('www.hel.fi', 'http'),
      ['fi' => 'test-path'],
      EnvironmentEnum::Local,
    );
    $this->assertEquals('https://www.hel.fi/test-path', $sut->getUrl('fi'));
    $this->assertEquals('http://www.hel.fi/test-path', $sut->getInternalAddress('fi'));

    $sut = new Environment(
      new Address('www.hel.fi'),
      new Address('www.hel.fi', 'https', 8080),
      ['fi' => 'test-path'],
      EnvironmentEnum::Local,
    );
    $this->assertEquals('https://www.hel.fi/test-path', $sut->getUrl('fi'));
    $this->assertEquals('https://www.hel.fi:8080/test-path', $sut->getInternalAddress('fi'));
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\helfi_api_base\Environment\Address;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\EnvironmentMetadata;
use Drupal\Tests\UnitTestCase;

/**
 * Tests environment value object.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\Environment
 * @group helfi_api_base
 */
class EnvironmentTest extends UnitTestCase {

  /**
   * @covers ::__construct
   * @covers ::getId
   * @covers ::getEnvironment
   * @covers ::getEnvironmentName
   * @covers ::getInternalBaseUrl
   * @covers ::getBaseUrl
   * @cove
   * @covers ::getMetadata
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::getOpenshiftConsoleLink
   */
  public function testSimpleGetters() : void {
    $sut = new Environment(
      new Address('www.hel.fi'),
      new Address('internal-address.local', 'http', 8080),
      [],
      'test',
      EnvironmentEnum::Test,
      new EnvironmentMetadata('https://localhost'));
    $this->assertEquals('test', $sut->getId());
    $this->assertEquals(EnvironmentEnum::Test, $sut->getEnvironment());
    $this->assertEquals(EnvironmentEnum::Test->value, $sut->getEnvironmentName());
    $this->assertEquals('https://www.hel.fi', $sut->getBaseUrl());
    $this->assertEquals('http://internal-address.local:8080', $sut->getInternalBaseUrl());
    $this->assertEquals('https://localhost', $sut->getMetadata()->getOpenshiftConsoleLink());
  }

  /**
   * @covers ::__construct
   * @covers ::getUrl
   * @covers ::getPath
   * @covers ::getInternalAddress
   */
  public function testGetUrl() : void {
    $sut = new Environment(
      new Address('www.hel.fi'),
      new Address('www.hel.fi', 'http'),
      ['fi' => 'test-path'],
      'test',
      EnvironmentEnum::Local,
      NULL);
    $this->assertEquals('https://www.hel.fi/test-path', $sut->getUrl('fi'));
    $this->assertEquals('http://www.hel.fi/test-path', $sut->getInternalAddress('fi'));

    $caught = FALSE;
    try {
      $sut->getUrl('en');
    }
    catch (\InvalidArgumentException $e) {
      $this->assertEquals('Path not found for "en" language.', $e->getMessage());
      $caught = TRUE;
    }
    $this->assertTrue($caught);

    $sut = new Environment(
      new Address('www.hel.fi'),
      new Address('www.hel.fi', 'https', 8080),
      ['fi' => 'test-path'],
      'test',
      EnvironmentEnum::Local,
      NULL);
    $this->assertEquals('https://www.hel.fi/test-path', $sut->getUrl('fi'));
    $this->assertEquals('https://www.hel.fi:8080/test-path', $sut->getInternalAddress('fi'));
  }

}

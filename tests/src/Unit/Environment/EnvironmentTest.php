<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\helfi_api_base\Environment\Address;
use Drupal\helfi_api_base\Environment\Environment;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\Tests\UnitTestCase;

/**
 * Tests environment value object.
 *
 * @group helfi_api_base
 */
class EnvironmentTest extends UnitTestCase {

  /**
   * Tests getters.
   */
  public function testSimpleGetters() : void {
    $sut = new Environment(
      new Address('www.hel.fi'),
      new Address('internal-address.local', 'http', 8080),
      [],
      EnvironmentEnum::Test,
    );
    $this->assertEquals(EnvironmentEnum::Test, $sut->getEnvironment());
    $this->assertEquals(EnvironmentEnum::Test->value, $sut->getEnvironmentName());
    $this->assertEquals('https://www.hel.fi', $sut->getBaseUrl());
    $this->assertEquals('http://internal-address.local:8080', $sut->getInternalBaseUrl());
  }

  /**
   * Test path validation.
   */
  public function testGetUrlException() : void {
    $sut = new Environment(
      new Address('www.hel.fi'),
      new Address('www.hel.fi', 'http'),
      ['fi' => 'test-path'],
      EnvironmentEnum::Local,
    );
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

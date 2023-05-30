<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

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
   * @covers ::getProtocol
   * @covers ::getDomain
   * @covers ::getEnvironment
   * @covers ::getEnvironmentName
   * @covers ::getBaseUrl
   * @covers ::getMetadata
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::__construct
   * @covers \Drupal\helfi_api_base\Environment\EnvironmentMetadata::getOpenshiftConsoleLink
   */
  public function testSimpleGetters() : void {
    $sut = new Environment('www.hel.fi', [], 'https', 'test', EnvironmentEnum::Test, new EnvironmentMetadata('https://localhost'));
    $this->assertEquals('test', $sut->getId());
    $this->assertEquals('www.hel.fi', $sut->getDomain());
    $this->assertEquals('https', $sut->getProtocol());
    $this->assertEquals(EnvironmentEnum::Test, $sut->getEnvironment());
    $this->assertEquals(EnvironmentEnum::Test->value, $sut->getEnvironmentName());
    $this->assertEquals('https://www.hel.fi', $sut->getBaseUrl());
    $this->assertEquals('https://localhost', $sut->getMetadata()->getOpenshiftConsoleLink());
  }

  /**
   * @covers ::__construct
   * @covers ::getUrl
   * @covers ::doGetUrl
   * @covers ::getPath
   * @covers ::getInternalAddress
   * @covers ::getBaseUrl
   * @covers ::getDomain
   * @covers ::getProtocol
   */
  public function testGetUrl() : void {
    $sut = new Environment('www.hel.fi', ['fi' => 'test-path'], 'https', 'test', EnvironmentEnum::Local, NULL);
    $this->assertEquals('https://www.hel.fi/test-path', $sut->getUrl('fi'));
    $this->assertEquals('https://www.hel.fi/test-path', $sut->getInternalAddress('fi'));

    $caught = FALSE;
    try {
      $sut->getUrl('en');
    }
    catch (\InvalidArgumentException $e) {
      $this->assertEquals('Path not found for "en" language.', $e->getMessage());
      $caught = TRUE;
    }
    $this->assertTrue($caught);

    $sut = new Environment('www.hel.fi:8080', ['fi' => 'test-path'], 'https', 'test', EnvironmentEnum::Local, NULL);
    $this->assertEquals('https://www.hel.fi/test-path', $sut->getUrl('fi'));
    $this->assertEquals('https://www.hel.fi:8080/test-path', $sut->getInternalAddress('fi'));
  }

}

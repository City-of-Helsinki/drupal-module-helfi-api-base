<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Link\InternalDomainResolver;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests internal domain resolver.
 *
 * @group helfi_api_base
 * @coversDefaultClass \Drupal\helfi_api_base\Link\InternalDomainResolver
 */
class InternalDomainResolverTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $container = new ContainerBuilder();
    $container->set('path.validator', $this->createMock(PathValidatorInterface::class));
    \Drupal::setContainer($container);
  }

  /**
   * Tests whether the url is external or not.
   *
   * @covers ::isExternal
   * @covers ::__construct
   * @covers ::getDomains
   */
  #[DataProvider(methodName: 'isExternalData')]
  public function testIsExternal(?string $url, bool $expectedExternal) : void {
    $url = Url::fromUri($url);
    $sut = new InternalDomainResolver([
      'www.hel.fi',
      '*.docker.so',
      'avustukset.hel.fi',
    ]);
    $this->assertEquals($expectedExternal, $sut->isExternal($url));
  }

  /**
   * Data provider for ::testIsExternal().
   *
   * @return array[]
   *   The data.
   */
  public static function isExternalData() : array {
    return [
      ['entity:node/1', FALSE],
      ['internal:/test', FALSE],
      ['https://example.com', TRUE],
      ['https://www.hel.fi', FALSE],
      ['https://kymp.docker.so', FALSE],
      ['https://helfi-proxy.docker.so/test', FALSE],
      ['tel:+358123456', TRUE],
      ['mailto:admin@example.com', TRUE],
    ];
  }

  /**
   * Tests getProtocol().
   *
   * @covers ::__construct
   * @covers ::getDomains
   * @covers ::getProtocol
   */
  #[DataProvider(methodName: 'getProtocolData')]
  public function testGetProtocol(string $url, ?string $expected) : void {
    $url = Url::fromUri($url);
    $sut = new InternalDomainResolver([
      'www.hel.fi',
      '*.docker.so',
      'avustukset.hel.fi',
    ]);
    $this->assertEquals($expected, $sut->getProtocol($url));
  }

  /**
   * Data provider for testGetProtocol().
   *
   * @return array[]
   *   The data.
   */
  public static function getProtocolData() : array {
    return [
      ['entity:node/1', NULL],
      ['internal:/test', NULL],
      ['https://example.com', NULL],
      ['https://www.hel.fi', NULL],
      ['tel:+123456', 'tel'],
      ['mailto:admin@example.com', 'mailto'],
      ['steam://test', 'steam'],
    ];
  }

}

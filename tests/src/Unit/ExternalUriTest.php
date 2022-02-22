<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\helfi_api_base\Helper\ExternalUri;
use Drupal\Tests\UnitTestCase;

/**
 * Tests external uri helper.
 *
 * @group helfi_api_base
 * @coversDefaultClass \Drupal\helfi_api_base\Helper\ExternalUri
 */
class ExternalUriTest extends UnitTestCase {

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
   * @dataProvider isExternalData
   */
  public function testIsExternal(string $url, bool $expectedExternal) : void {
    $url = Url::fromUri($url);
    // Mark www.hel.fi as internal.
    $sut = new ExternalUri($url, ['www.hel.fi']);
    $this->assertEquals($expectedExternal, $sut->isExternal());
  }

  /**
   * Data provider for ::testIsExternal().
   *
   * @return array[]
   *   The data.
   */
  public function isExternalData() : array {
    return [
      ['entity:node/1', FALSE],
      ['internal:/test', FALSE],
      ['https://example.com', TRUE],
      ['https://www.hel.fi', FALSE],
    ];
  }

}

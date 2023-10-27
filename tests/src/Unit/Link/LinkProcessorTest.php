<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\Link;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\GeneratedLink;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\helfi_api_base\Link\InternalDomainResolver;
use Drupal\helfi_api_base\Link\LinkProcessor;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Link\LinkProcessor
 * @group helfi_api_base
 */
class LinkProcessorTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * @covers ::preRenderLink
   * @covers \Drupal\helfi_api_base\Link\InternalDomainResolver::__construct
   * @covers \Drupal\helfi_api_base\Link\InternalDomainResolver::getProtocol
   * @covers \Drupal\helfi_api_base\Link\InternalDomainResolver::isExternal
   */
  public function testPreRenderLink() : void {
    $container = new ContainerBuilder();
    $container->set('helfi_api_base.internal_domain_resolver', new InternalDomainResolver(['www.hel.fi']));
    $container->set('url_generator', $this->prophesize(UrlGeneratorInterface::class)->reveal());
    $linkGenerator = $this->prophesize(LinkGeneratorInterface::class);
    $linkGenerator->generate(Argument::any(), Argument::any())
      ->willReturn(new GeneratedLink());
    $container->set('link_generator', $linkGenerator->reveal());
    \Drupal::setContainer($container);

    $url = Url::fromUri('tel:123456', ['absolute' => TRUE]);

    $element = [
      '#url' => $url,
      '#title' => 'something',
    ];
    $render = LinkProcessor::preRenderLink($element);
    $this->assertEquals([
      'data-is-external' => 'true',
      'data-protocol' => 'tel',
    ], $render['#attributes']);
  }

}

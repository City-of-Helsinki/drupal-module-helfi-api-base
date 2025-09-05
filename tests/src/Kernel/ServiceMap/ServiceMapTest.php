<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\ServiceMap;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\helfi_api_base\ServiceMap\DTO\Address;
use Drupal\helfi_api_base\ServiceMap\ServiceMap;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Tests linked events helper service.
 */
class ServiceMapTest extends KernelTestBase {

  use ProphecyTrait;
  use ApiTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'helfi_api_base',
  ];

  /**
   * Gets the SUT.
   *
   * @param \Prophecy\Prophecy\ObjectProphecy $client
   *   The client mock.
   * @param \Prophecy\Prophecy\ObjectProphecy $logger
   *   The logger mock.
   *
   * @return \Drupal\helfi_api_base\ServiceMap\ServiceMap
   *   The SUT.
   */
  private function getSut(ObjectProphecy $client, ObjectProphecy $logger) : ServiceMap {
    $sut = new ServiceMap(
      $client->reveal(),
      $this->container->get(LanguageManagerInterface::class),
      $logger->reveal(),
    );
    return $sut;
  }

  /**
   * Tests failing request.
   */
  public function testQueryGuzzleException() : void {
    $client = $this->prophesize(ClientInterface::class);
    $client->request('GET', Argument::any(), Argument::any())
      ->shouldBeCalled()
      ->willThrow(
        new ClientException(
          'Fail.',
          $this->prophesize(RequestInterface::class)->reveal(),
          new Response(400)
        )
      );
    $logger = $this->prophesize(LoggerInterface::class);
    $logger->log(LogLevel::ERROR, Argument::any(), Argument::any())->shouldBeCalled();

    $sut = $this->getSut($client, $logger);
    $this->assertEmpty($sut->query('123'));
  }

  /**
   * Tests query().
   */
  public function testQuery() : void {
    $client = $this->prophesize(ClientInterface::class);
    $client->request('GET', Argument::any(), Argument::any())
      ->shouldBeCalled()
      ->willReturn(
        new Response(body: ''),
        new Response(body: json_encode(['results' => []])),
      );

    $logger = $this->prophesize(LoggerInterface::class);
    $sut = $this->getSut($client, $logger);
    // Make sure the first request fails to empty results.
    $this->assertEmpty($sut->query('123'));
  }

  /**
   * Tests getAddressData().
   */
  public function testGetAddressData() : void {
    $client = $this->prophesize(ClientInterface::class);
    $client->request('GET', Argument::any(), Argument::any())
      ->shouldBeCalled()
      ->willReturn(
        new Response(body: ''),
        new Response(body: json_encode([
          'results' => [
            [
              'name' => ['fi' => '123'],
              'location' => ['coordinates' => [123, 321], 'type' => 'Point'],
            ],
          ],
        ])),
      );
    $logger = $this->prophesize(LoggerInterface::class);
    $sut = $this->getSut($client, $logger);
    $this->assertNull($sut->getAddressData('123'));
    $address = $sut->getAddressData('123');
    $this->assertInstanceOf(Address::class, $address);

    $this->assertEquals('123', $address->streetName->getName('fi'));
    $this->assertEquals(123, $address->location->lon);
    $this->assertEquals(321, $address->location->lat);
    $this->assertEquals('Point', $address->location->type);
  }

}

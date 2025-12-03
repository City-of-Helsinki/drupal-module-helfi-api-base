<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\ServiceMap\Controller;

use Drupal\helfi_api_base\ServiceMap\Controller\AutocompleteController;
use Drupal\helfi_api_base\ServiceMap\DTO\Address;
use Drupal\helfi_api_base\ServiceMap\DTO\Location;
use Drupal\helfi_api_base\ServiceMap\DTO\StreetName;
use Drupal\helfi_api_base\ServiceMap\ServiceMap;
use Drupal\helfi_api_base\ServiceMap\ServiceMapInterface;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Kernel test for AutocompleteController.
 *
 * @group helfi_api_base
 */
class AutocompleteControllerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'system',
  ];

  /**
   * The controller to test.
   */
  protected AutocompleteController $controller;

  /**
   * Mocked ServiceMap.
   */
  protected ServiceMap|MockObject $serviceMap;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->serviceMap = $this->createMock(ServiceMapInterface::class);
    $this->controller = new AutocompleteController($this->serviceMap);
  }

  /**
   * Tests the addressSuggestions method.
   */
  public function testAddressSuggestions() {
    $request = new Request([
      'q' => 'Kalev',
    ]);
    $this->serviceMap->expects(self::once())
      ->method('query')
      ->willReturn(array_map(
        function ($name) {
          return new Address(
            StreetName::createFromArray(['fi' => $name]),
            new Location(60.171, 24.934, 'Point'),
          );
        },
        [
          'Kalevankatu 2',
          'Lönnrotinkatu 3',
          'Aleksanterinkatu 20',
        ]),
      );

    $addressSuggestions = $this->controller->addressSuggestions($request);
    $this->assertInstanceOf(JsonResponse::class, $addressSuggestions);
  }

}

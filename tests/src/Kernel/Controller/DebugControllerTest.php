<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Controller;

use Drupal\helfi_api_base\Debug\SupportsValidityChecksInterface;
use Drupal\helfi_api_base\DebugDataItemInterface;
use Drupal\helfi_api_base\DebugDataItemPluginManager;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Drupal\helfi_api_base\Controller\DebugController;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests Debug controller.
 *
 * @group helfi_api_base
 */
class DebugControllerTest extends ApiKernelTestBase {

  use ProphecyTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'serialization',
    'rest',
  ];

  /**
   * Tests controller permissions.
   */
  public function testControllerPermission() : void {
    $this->drupalSetUpCurrentUser();
    $request = $this->getMockedRequest('/admin/debug');
    $response = $this->processRequest($request);

    $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

    $request = $this->getMockedRequest('/api/v1/debug/composer');
    $response = $this->processRequest($request);

    $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

    $this->drupalSetUpCurrentUser(permissions: ['access debug page']);
    $request = $this->getMockedRequest('/admin/debug');
    $response = $this->processRequest($request);

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
  }

  /**
   * Tests build.
   */
  public function testBuild() : void {
    $sut = DebugController::create($this->container);
    $build = $sut->build();

    $this->assertArrayHasKey('migrate', $build);
    $this->assertArrayHasKey('composer', $build);
  }

  /**
   * Tests build.
   */
  public function testApi() : void {
    $plugin = $this->prophesize(SupportsValidityChecksInterface::class);
    $plugin
      ->check()
      ->willReturn(TRUE, FALSE);

    $manager = $this->prophesize(DebugDataItemPluginManager::class);
    $manager
      ->createInstance('test')
      ->willReturn($plugin->reveal());

    $this->container->set(DebugDataItemPluginManager::class, $manager->reveal());

    $this->drupalSetUpCurrentUser(permissions: ['access debug api']);

    $request = $this->getMockedRequest('/api/v1/debug/test');

    $response = $this->processRequest($request);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    $response = $this->processRequest($request);
    $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
  }

}

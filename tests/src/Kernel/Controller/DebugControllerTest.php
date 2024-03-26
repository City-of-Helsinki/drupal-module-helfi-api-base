<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Controller;

use Drupal\helfi_api_base\Controller\DebugController;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
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

    $this->drupalSetUpCurrentUser(permissions: ['restful get helfi_debug_data']);
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
    $this->assertArrayHasKey('maintenance-mode', $build);
  }

}

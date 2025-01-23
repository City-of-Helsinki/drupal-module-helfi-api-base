<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Symfony\Component\HttpFoundation\Response;

/**
 * Tests monolog configuration.
 */
final class AssetVersioningTest extends ApiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'asset_version_test',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function bootKernel() : void {
    $this->setSetting('deployment_identifier', 'test-123');
    parent::bootKernel();
  }

  /**
   * Tests asset version.
   */
  public function testAssetVersioning() : void {
    $request = $this->getMockedRequest('/test-controller');
    $response = $this->processRequest($request);

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    // Check that HELFI_DEPLOYMENT_IDENTIFIER in
    // module libraries.yml is correctly replaced.
    $this->assertStringContainsString("my-library.js?v=test-123", $response->getContent());
  }

}

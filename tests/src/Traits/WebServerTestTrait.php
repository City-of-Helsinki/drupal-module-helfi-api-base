<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

use donatj\MockWebServer\MockWebServer;

if (!defined('STDIN')) {
  define('STDIN', fopen('php://stdin', 'r'));
}

/**
 * Provides shared functionality for web server tests.
 */
trait WebServerTestTrait {

  /**
   * The mock webserver.
   *
   * @var \donatj\MockWebServer\MockWebServer
   */
  protected ?MockWebServer $webServer;

  /**
   * Creates a mock web server.
   */
  protected function startWebServer() : void {
    $this->webServer = new MockWebServer();
    $this->webServer->start();
  }

  /**
   * Stops the mock web server.
   */
  protected function stopWebServer() : void {
    $this->webServer->stop();
  }

  /**
   * Gets the base url for mock web server.
   *
   * @param string $path
   *   The path.
   *
   * @return string
   *   The base url.
   */
  protected function getMockWebServerBaseUrl(string $path) : string {
    return vsprintf('%s/%s', [
      $this->webServer->getServerRoot(),
      $path,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->startWebServer();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    parent::tearDown();
    $this->stopWebServer();
  }

}

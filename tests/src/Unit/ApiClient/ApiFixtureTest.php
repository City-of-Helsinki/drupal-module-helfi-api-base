<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Core\File\Exception\FileNotExistsException;
use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\ApiClient\ApiFixture;
use Drupal\helfi_api_base\ApiClient\ApiResponse;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\ApiClient\ApiFixture
 * @group helfi_api_base
 * */
class ApiFixtureTest extends UnitTestCase {

  /**
   * Test fixture loading.
   *
   * @covers ::requestFromFile
   * @covers \Drupal\helfi_api_base\ApiClient\ApiResponse
   */
  public function testFixtures() {
    $response = ApiFixture::requestFromFile(vsprintf('%s/../../../fixtures/response.json', [
      __DIR__,
    ]));

    $this->assertInstanceOf(ApiResponse::class, $response);
  }

  /**
   * Test missing file.
   *
   * @covers ::requestFromFile
   */
  public function testException() {
    $this->expectException(FileNotExistsException::class);
    ApiFixture::requestFromFile(vsprintf('%s/should-not-exists', [
      __DIR__,
    ]));
  }

}

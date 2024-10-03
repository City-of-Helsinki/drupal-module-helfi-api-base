<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Routing;

use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Drupal\remote_entity_test\Entity\RemoteEntityRevisionTest;

/**
 * Tests revision route provider.
 *
 * @group helfi_api_base
 */
class RevisionRouteProviderTest extends ApiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'remote_entity_test',
    'entity',
    'user',
    'system',
  ];

  /**
   * The remote entity to test.
   *
   * @var \Drupal\remote_entity_test\Entity\RemoteEntityRevisionTest|null
   */
  private ?RemoteEntityRevisionTest $rmt = NULL;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('rmert_test');
    $this->installEntitySchema('user');
    $this->rmt = RemoteEntityRevisionTest::create([
      'id' => 1,
      'title' => 'Test 1',
    ]);
    $this->rmt->setPublished()
      ->save();
    $this->drupalSetUpCurrentUser(['uid' => 0]);
  }

  /**
   * Tests 'version-history' route.
   */
  public function testRevisionHistoryRoute() : void {
    $url = $this->rmt->toUrl('version-history');
    // Make sure a user without permission has no access.
    $this->assertFalse($url->access());

    // Make sure a user with permissions has access.
    $this->drupalSetUpCurrentUser(permissions: [
      'view all rmert_test revisions',
      'view rmert_test',
    ]);
    $url = $this->rmt->toUrl('version-history');
    $this->assertTrue($url->access());
  }

  /**
   * Tests revision-revert-language-form route.
   */
  public function testRevisionRevertLanguageFormRoute() : void {
    $url = $this->rmt->toUrl('revision-revert-language-form');
    // Make sure a user without permission has no access.
    $this->assertFalse($url->access());

    // Make sure a user with permissions has access.
    $this->drupalSetUpCurrentUser(permissions: [
      'revert all rmert_test revisions',
      'update any rmert_test',
    ]);
    $url = $this->rmt->toUrl('revision-revert-language-form');
    $this->assertTrue($url->access());
  }

}

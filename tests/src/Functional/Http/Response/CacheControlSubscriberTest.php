<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Tests\BrowserTestBase as CoreBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests debug data rest resource.
 */
#[RunTestsInSeparateProcesses]
#[Group('helfi_api_base')]
class CacheControlSubscriberTest extends CoreBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dynamic_page_cache',
    'dynamic_page_cache_test',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that CacheControlSubscriber is not run unless page cache is set.
   */
  public function testNoCacheControl() : void {
    $this->drupalGet('/dynamic-page-cache-test/html');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderNotContains('Cache-Control', 's-maxage');

  }

  /**
   * Tests that CacheControlSubscriber returns correct Cache-Control headers.
   */
  public function testCacheControlHeader() : void {
    $this->config('system.performance')
      ->set('cache.page.max_age', 3600)
      ->save();

    $this->drupalGet('/dynamic-page-cache-test/html');
    $headers = $this->getSession()->getResponseHeaders();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Cache-Control', 'max-age=0, must-revalidate, public, s-maxage=3600');
  }

}

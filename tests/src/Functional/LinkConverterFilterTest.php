<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\filter\Entity\FilterFormat;

/**
 * Tests link converter filter with site prefixes.
 *
 * @group helfi_api_base
 */
class LinkConverterFilterTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'node',
    'filter_test',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    FilterFormat::load('full_html')
      ->setFilterConfig('helfi_link_converter', ['status' => 1])
      ->save();
    $this->drupalCreateContentType(['type' => 'page']);
  }

  /**
   * Tests that language prefixes are added to the links in text fields.
   */
  public function testFilter() : void {
    $body = '
     <p>Test content with <a class="test-link" href="/rekry/test">Rekry</a> link.</p>
     <p>External link test:
       <a class="external-link" href="https://example.com" data-test="123">External link 1</a>
     </p>
     <p>External whitelisted link test:
       <a class="whitelisted-external-link" href="https://www.hel.fi">External link 2</a>
     </p>
     <p>External link without scheme:
       <a class="external-no-scheme" href="www.hel.fi">External link 3</a>
     </p>
     <p>Base link:
       <a class="base-link" href="base:/node/1">Base link</a>
     </p>
     <p>Entity link:
       <a class="entity-link" href="entity:node/1">Entity link</a>
     </p>
    ';
    $node = $this->drupalCreateNode([
      'title' => 'Test title',
      'body' => [
        'value' => $body,
        'format' => 'full_html',
      ],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl('canonical'));

    // Make sure attributes are not removed.
    $this->assertSession()
      ->elementAttributeContains('css', '.test-link', 'href',
        "/rekry/test"
      );
    $this->assertSession()
      ->elementAttributeContains('css', '.external-link', 'data-test', '123');
    // Make sure external links get a data-attribute to indicate it.
    $this->assertSession()
      ->elementAttributeContains('css', '.external-link', 'data-is-external', 'true');
    // Make sure whitelisted external URLs are not marked as external.
    $element = $this->getSession()->getPage()->find('css', '.whitelisted-external-link');
    $this->assertFalse($element->hasAttribute('data-is-external'));

    // Make sure urls without scheme defaults to https.
    $element = $this->getSession()->getPage()->find('css', '.external-no-scheme');
    $this->assertEquals('https://www.hel.fi', $element->getAttribute('href'));

    // Make sure base:/node/1 converts to /node/1.
    $element = $this->getSession()->getPage()->find('css', '.base-link');
    $this->assertEquals('/node/1', $element->getAttribute('href'));

    // Make sure entity:node/1 converts to /node/1.
    $element = $this->getSession()->getPage()->find('css', '.entity-link');
    $this->assertEquals('/node/1', $element->getAttribute('href'));
  }

}

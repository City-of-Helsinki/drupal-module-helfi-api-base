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
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'link_template_test_theme';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Setup Filtered HTML text format.
    $filtered_html_format = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'filters' => [
        'filter_html' => [
          'status' => 1,
          'settings' => [
            'allowed_html' => '<p><a href data-test class><span class>',
          ],
        ],
        'filter_url' => [
          'status' => 1,
        ],
        'helfi_link_converter' => [
          'status' => 1,
        ],
      ],
    ]);
    $filtered_html_format->save();
    $this->drupalCreateContentType(['type' => 'page']);
  }

  /**
   * Tests 'helfi_link_converter' filter.
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
       <a class="external-no-scheme" href="www.example.com">External link no scheme</a>
     </p>
     <p>Base link:
       <a class="base-link" href="base:/node/1">Base link</a>
     </p>
     <p>Internal link:
       <a class="internal-link" href="internal:/node/1">Internal link</a>
     </p>
     <p>Entity link:
       <a class="entity-link" href="entity:/node/1">Entity link</a>
     </p>
     <p>Tel link:
       <a class="tel-link" href="tel:+358123456">Tel link</a>
     </p>
     <p>Mailto link:
       <a class="mailto-link" href="mailto:example@example.com">Mailto link</a>
     </p>
     <p>
      <a class="no-href">No href link</a>
    </p>
     <p>
      <a class="nested-dom-link" href="/"><span class="nested" onload="alert(123);">Nested dom link</span></a>
    </p>
    ';
    $node = $this->drupalCreateNode([
      'title' => 'Test title',
      'body' => [
        'value' => $body,
        'format' => 'filtered_html',
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
    $element = $this->getSession()->getPage()->find('css', '.external-link');
    $this->assertEquals('true', $element->getAttribute('data-is-external'));
    $this->assertFalse($element->hasAttribute('data-protocol'));
    // Make sure  there's an external link text inside the link tag.
    $children = $element->find('css', '.helfi-external-link');
    $this->assertEquals('This is external link', $children->getText());

    // Make sure links without host are not marked as external.
    $element = $this->getSession()->getPage()->find('css', '.no-href');
    $this->assertFalse($element->hasAttribute('data-protocol'));
    $this->assertFalse($element->hasAttribute('data-is-external'));

    // Make sure whitelisted external URLs are not marked as external.
    $element = $this->getSession()->getPage()->find('css', '.whitelisted-external-link');
    $this->assertFalse($element->hasAttribute('data-protocol'));
    $this->assertFalse($element->hasAttribute('data-is-external'));

    // Make sure urls without scheme default to https://.
    $element = $this->getSession()->getPage()->find('css', '.external-no-scheme');
    $this->assertEquals('https://www.example.com', $element->getAttribute('href'));
    $this->assertFalse($element->hasAttribute('data-protocol'));
    $this->assertTrue($element->hasAttribute('data-is-external'));

    foreach (['base', 'entity', 'internal'] as $type) {
      // Make sure $type:/node/1 converts to /node/1.
      $element = $this->getSession()->getPage()->find('css', sprintf('.%s-link', $type));
      $this->assertEquals('/node/1', $element->getAttribute('href'));
      $this->assertFalse($element->hasAttribute('data-protocol'));
    }

    // Make sure tel and mailto links have 'data-protocol' scheme.
    foreach (['mailto', 'tel'] as $type) {
      $element = $this->getSession()->getPage()->find('css', sprintf('.%s-link', $type));
      $this->assertEquals($type, $element->getAttribute('data-protocol'));
      $children = $element->find('css', sprintf('.helfi-%s-link', $type));
      $this->assertEquals(sprintf('This is %s link', $type), $children->getText());
      $this->assertFalse($children->hasAttribute('data-is-external'));
    }
    $element = $this->getSession()->getPage()->find('css', '.nested-dom-link');
    $children = $element->find('css', '.nested');
    // Make sure nested tags get run through filter.
    $this->assertFalse($children->hasAttribute('onload'));
  }

}

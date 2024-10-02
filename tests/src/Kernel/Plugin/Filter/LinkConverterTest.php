<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Plugin;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\filter\FilterPluginCollection;
use Drupal\filter\FilterProcessResult;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\TestLoggerTrait;

/**
 * Tests custom language negotiator functionality.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\Filter\LinkConverter
 * @group helfi_api_base
 */
class LinkConverterTest extends KernelTestBase {

  use TestLoggerTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'field',
    'filter',
    'system',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('system', 'sequences');
    $this->installConfig('filter');
    $this->installConfig('system');
    $this->setUpMockLogger();
  }

  /**
   * @covers ::create
   * @covers ::process
   * @covers ::render
   * @covers ::getLinkText
   * @covers ::getNodeAttributes
   */
  public function testInvalidLink() : void {
    $this->expectLogMessage('Failed to parse link: dsa');
    $this->processText('<a href="@">dsa</a>');
  }

  /**
   * @covers ::create
   * @covers ::process
   * @covers ::render
   * @covers ::getLinkText
   * @covers ::getNodeAttributes
   * @dataProvider linkProcessingData
   */
  public function testLinkProcessing(string $expected, string $text) : void {
    $result = $this->processText($text, 'en');
    $output = $result->getProcessedText();

    $this->assertStringStartsWith($expected, $output);
  }

  /**
   * The data provider for testLinkProcessing().
   *
   * @return array[]
   *   The data.
   */
  public function linkProcessingData() : array {
    return [
      [
        '<a href="/jotain">',
        '<a href="/jotain">text</a>',
      ],
      [
        '<a href="https://www.hel.fi/en/jotain" class="test">',
        '<a href="https://www.hel.fi/en/jotain" class="test">dsada</a>',
      ],
      [
        '<a href="https://google.com" class="test" data-is-external="true">',
        '<a href="https://google.com" class="test">dsada</a>',
      ],
      [
        '<a href="tel:123456" class="test" data-is-external="true" data-protocol="tel">',
        '<a href="tel:123456" class="test">dsada</a>',
      ],
      [
        '<a href="mailto:admin@example.com" class="test" data-test="123" data-is-external="true" data-protocol="mailto">',
        '<a href="mailto:admin@example.com" class="test" data-test="123">dsada</a>',
      ],
    ];
  }

  /**
   * Processes text through the provided filters.
   *
   * @param string $text
   *   The text string to be filtered.
   * @param string $langcode
   *   The language code of the text to be filtered.
   * @param string[] $filter_ids
   *   (optional) The filter plugin IDs to apply to the given text, in the order
   *   they are being requested to be executed.
   *
   * @return \Drupal\filter\FilterProcessResult
   *   The filtered text, wrapped in a FilterProcessResult object, and possibly
   *   with associated assets, cacheability metadata and placeholders.
   *
   * @see \Drupal\filter\Element\ProcessedText::preRenderText()
   */
  protected function processText(
    string $text,
    string $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED,
    array $filter_ids = [
      'helfi_link_converter',
    ],
  ) : FilterProcessResult {
    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager);
    $filters = [];
    foreach ($filter_ids as $filter_id) {
      $filters[] = $bag->get($filter_id);
    }

    $render_context = new RenderContext();
    /** @var \Drupal\filter\FilterProcessResult $filter_result */
    $filter_result = $this->container->get('renderer')->executeInRenderContext($render_context, function () use ($text, $filters, $langcode) {
      $metadata = new BubbleableMetadata();
      foreach ($filters as $filter) {
        /** @var \Drupal\filter\FilterProcessResult $result */
        $result = $filter->process($text, $langcode);
        $metadata = $metadata->merge($result);
        $text = $result->getProcessedText();
      }
      return (new FilterProcessResult($text))->merge($metadata);
    });
    if (!$render_context->isEmpty()) {
      $filter_result = $filter_result->merge($render_context->pop());
    }
    return $filter_result;
  }

}

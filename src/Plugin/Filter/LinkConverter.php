<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Link converter' filter.
 *
 * @Filter(
 *   id = "helfi_link_converter",
 *   title = @Translation("Hel.fi: Link converter"),
 *   description = @Translation("Runs embedded links through a template. NOTE: This filter must be run after 'Convert URLs into links' filter."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   settings = {
 *   },
 *   weight = -10
 * )
 */
final class LinkConverter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private RendererInterface $renderer;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private LanguageManagerInterface $languageManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) : self {
    $instance = new self($configuration, $plugin_id, $plugin_definition);
    $instance->renderer = $container->get('renderer');
    $instance->languageManager = $container->get('language_manager');
    $instance->logger = $container->get('logger.channel.helfi_api_base');

    return $instance;
  }

  /**
   * Parses the embedded url.
   *
   * @param string $value
   *   The url.
   *
   * @return \Drupal\Core\Url
   *   The URL.
   */
  private function parseEmbeddedUrl(string $value) : Url {
    if (str_starts_with($value, '/')) {
      return Url::fromUserInput($value);
    }
    if (!parse_url($value, PHP_URL_SCHEME)) {
      $value = sprintf('https://%s', $value);
    }
    return Url::fromUri($value);
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) : FilterProcessResult {
    $result = new FilterProcessResult($text);
    $dom = Html::load($text);

    $hasChanges = FALSE;

    /** @var \DOMElement $node */
    foreach ($dom->getElementsByTagName('a') as $node) {
      $hasChanges = TRUE;
      // Nothing to do if link has no href.
      if (!$value = $node->getAttribute('href')) {
        continue;
      }

      try {
        $build = Link::fromTextAndUrl($node->nodeValue, $this->parseEmbeddedUrl($value))
          ->toRenderable();
      }
      catch (\InvalidArgumentException $e) {
        $this->logger->error(
          sprintf('Failed to parse link: %s', $node->nodeValue)
        );
        continue;
      }

      $build['#attributes']['class'] = [];
      // Any attributes not consumed by the filter should be carried over to the
      // rendered item.
      foreach ($node->attributes as $attribute) {
        if ($attribute->nodeName == 'class') {
          // We don't want to overwrite the existing CSS class.
          $build['#attributes']['class'] = array_unique(array_merge($build['#attributes']['class'], explode(' ', $attribute->nodeValue)));
        }
        else {
          $build['#attributes'][$attribute->nodeName] = $attribute->nodeValue;
        }
      }
      unset($build['#attributes']['href']);
      $this->render($build, $node, $result);
    }

    if ($hasChanges) {
      $result->setProcessedText(Html::serialize($dom));
    }
    return $result;
  }

  /**
   * Renders the given render array into the given DOM node.
   *
   * @param array $build
   *   The render array to render in isolation.
   * @param \DOMNode $node
   *   The DOM node to render into.
   * @param \Drupal\filter\FilterProcessResult $result
   *   The accumulated result of filter processing, updated with the metadata
   *   bubbled during rendering.
   */
  private function render(array $build, \DOMNode $node, FilterProcessResult &$result) : void {
    // We need to render the data:
    // - without replacing placeholders, so that the placeholders are
    //   only replaced at the last possible moment. Hence we cannot use
    //   either renderPlain() or renderRoot(), so we must use render().
    // - without bubbling beyond this filter, because filters must
    //   ensure that the bubbleable metadata for the changes they make
    //   when filtering text makes it onto the FilterProcessResult
    //   object that they return ($result). To prevent that bubbling, we
    //   must wrap the call to render() in a render context.
    $markup = (string) $this->renderer->executeInRenderContext(new RenderContext(), function () use (&$build) {
      return $this->renderer->render($build);
    });
    $result = $result->merge(BubbleableMetadata::createFromRenderArray($build));

    // Load the content into a new DOMDocument and retrieve the DOM nodes.
    $replacement_nodes = Html::load($markup)->getElementsByTagName('body')
      ->item(0)
      ->childNodes;

    foreach ($replacement_nodes as $replacement_node) {
      // Import the replacement node from the new DOMDocument into the original
      // one, importing also the child nodes of the replacement node.
      $replacement_node = $node->ownerDocument->importNode($replacement_node, TRUE);
      $node->parentNode->insertBefore($replacement_node, $node);
    }
    $node->parentNode->removeChild($node);
  }

}

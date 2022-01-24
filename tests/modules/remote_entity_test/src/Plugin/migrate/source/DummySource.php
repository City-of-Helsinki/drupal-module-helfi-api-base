<?php

declare(strict_types = 1);

namespace Drupal\remote_entity_test\Plugin\migrate\source;

use Drupal\helfi_api_base\Plugin\migrate\source\HttpSourcePluginBase;

/**
 * Dummy source plugin.
 *
 * @MigrateSource(
 *   id = "dummy_source"
 * )
 */
final class DummySource extends HttpSourcePluginBase {

  /**
   * {@inheritdoc}
   */
  protected function initializeListIterator(): \Iterator {
    yield [];
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return 'DummySource';
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'id' => [
        'type' => 'string',
      ],
    ];
  }

}

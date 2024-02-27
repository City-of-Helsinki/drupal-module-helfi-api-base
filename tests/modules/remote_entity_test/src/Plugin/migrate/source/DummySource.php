<?php

declare(strict_types=1);

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
    yield ['id' => 1, 'language' => 'fi', 'title' => 'Title fi 1'];
    yield ['id' => 1, 'language' => 'en', 'title' => 'Title en 1'];
    yield ['id' => 2, 'language' => 'en', 'title' => 'Title en 2'];
    yield ['id' => 2, 'language' => 'fi', 'title' => 'Title fi 2'];
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
      'language' => [
        'type' => 'string',
      ],
    ];
  }

}

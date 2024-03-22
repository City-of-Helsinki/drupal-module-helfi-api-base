<?php

declare(strict_types=1);

namespace Drupal\remote_entity_test\Fixture;

use Drupal\helfi_api_base\Fixture\FixtureBase;
use GuzzleHttp\Psr7\Response;

/**
 * Provides fixture data for tpr_unit migration.
 */
final class DummyMigrate extends FixtureBase {

  /**
   * {@inheritdoc}
   */
  public function getMockResponses() : array {
    return [
      new Response(200, [], json_encode([
        'id' => 1,
        'name' => 'Title 1',
      ])),
    ];
  }

}

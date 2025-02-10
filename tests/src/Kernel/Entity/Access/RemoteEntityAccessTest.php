<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Access;

use Drupal\helfi_api_base\Entity\RemoteEntityInterface;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;

/**
 * Tests remote entity access.
 *
 * @group helfi_api_base
 */
class RemoteEntityAccessTest extends RemoteEntityAccessTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'remote_entity_test',
    'menu_link_content',
    'user',
  ];

  /**
   * {@inheritDoc}
   */
  protected function setUpRemoteEntity(): RemoteEntityInterface {
    $this->installEntitySchema('remote_entity_test');

    return RemoteEntityTest::create([
      'id' => 1,
      'name' => 'Test 1',
    ]);
  }

}

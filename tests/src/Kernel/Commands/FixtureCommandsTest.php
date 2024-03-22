<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Commands;

use Drupal\helfi_api_base\Commands\FixtureCommands;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests Fixture command.
 *
 * @group helfi_api_base
 */
class FixtureCommandsTest extends ApiKernelTestBase {

  use ProphecyTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'remote_entity_test',
    'menu_link_content',
    'migrate',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('remote_entity_test');
  }

  /**
   * Tests Fixture migrate command.
   */
  public function testFixtureMigrate() : void {
    $this->assertNull(RemoteEntityTest::load(1));
    $sut = new FixtureCommands($this->container);
    $sut->migrateFixtures('dummy_migrate');
    $entity = RemoteEntityTest::load(1);
    $this->assertInstanceOf(RemoteEntityTest::class, $entity);
    $this->assertFalse($entity->isPublished());

    $sut->migrateFixtures('dummy_migrate', ['publish' => TRUE]);
    $entity = RemoteEntityTest::load(1);
    $this->assertTrue($entity->isPublished());
  }

}

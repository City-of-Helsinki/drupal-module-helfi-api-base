<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Tests\Kernel;

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\helfi_api_base\MigrateTrait;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;

/**
 * Tests the translatable migration destination plugin.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\migrate\destination\TranslatableEntity
 * @coversClass \Drupal\helfi_api_base\Plugin\Derivative\MigrateTranslatableEntity
 * @group helfi_api_base
 */
class TranslatableMigrationTest extends MigrationTestBase {

  use MigrateTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'remote_entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    $this->installEntitySchema('remote_entity_test');
  }

  /**
   * Tests that we can migrate multilingual entities.
   */
  public function testMigrate() : void {
    $this->executeMigration('dummy_migrate');
    /** @var \Drupal\remote_entity_test\Entity\RemoteEntityTest[] $entities */
    $entities = RemoteEntityTest::loadMultiple();
    $this->assertCount(2, $entities);

    // First available language should always be the default translation.
    $this->assertTrue($entities[1]->getTranslation('fi')->isDefaultTranslation());
    $this->assertTrue($entities[2]->getTranslation('en')->isDefaultTranslation());

    foreach ($entities as $id => $entity) {
      foreach (['fi', 'en'] as $language) {
        $this->assertTrue($entity->hasTranslation($language));
        $this->assertEquals("Title $language $id", $entity->getTranslation($language)->label());
      }
      // Make sure entity is unpublished because we migrate them as unpublished
      // by default.
      $this->assertFalse($entity->isPublished());
      $entity->setPublished()->save();
    }

    // Re-run migrate to make sure default values are not overridden.
    $this->executeMigration('dummy_migrate');
    /** @var \Drupal\remote_entity_test\Entity\RemoteEntityTest[] $entities */
    $entities = RemoteEntityTest::loadMultiple();

    foreach ($entities as $entity) {
      $this->assertTrue($entity->isPublished());
    }
  }

  /**
   * Tests that we can run a rollback on multilingual migrations.
   */
  public function testRollback() : void {
    $this->executeMigration('dummy_migrate');
    $this->assertCount(2, RemoteEntityTest::loadMultiple());

    // Make sure entities are deleted when we run rollback.
    $this->rollbackMigration('dummy_migrate');
    $this->assertEmpty(RemoteEntityTest::loadMultiple());

    // Make sure entities can be migrated again.
    $this->executeMigration('dummy_migrate');
    $this->assertCount(2, RemoteEntityTest::loadMultiple());
  }

}

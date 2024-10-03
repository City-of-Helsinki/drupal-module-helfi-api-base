<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Revision;

use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\LanguageManagerTrait;
use Drupal\helfi_api_base\Entity\Revision\RevisionManager;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;

/**
 * Tests revision manager.
 *
 * @group helfi_api_base
 */
class RevisionManagerTest extends ApiKernelTestBase {

  use LanguageManagerTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_language_negotiator_test',
    'language',
    'user',
    'link',
    'menu_link_content',
    'system',
    'content_translation',
    'remote_entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setupLanguages();
    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('remote_entity_test');
  }

  /**
   * Sets the allowed revision entity types.
   *
   * @param array $entityTypes
   *   The entity types.
   */
  private function setEntityTypes(array $entityTypes): void {
    $this->config('helfi_api_base.delete_revisions')
      ->set('entity_types', $entityTypes)
      ->save();
  }

  /**
   * Gets the SUT.
   *
   * @return \Drupal\helfi_api_base\Entity\Revision\RevisionManager
   *   The sut.
   */
  private function getSut() : RevisionManager {
    return $this->container->get('helfi_api_base.revision_manager');
  }

  /**
   * Make sure an exception is throw when the entity type is not supported.
   */
  public function testEntityTypeNotSupportedException() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Entity type is not supported.');
    $this->getSut()->deleteRevisions('node', [1]);
  }

  /**
   * Make sure an exception is thrown when the entity type does not exist.
   */
  public function testDeletePreviousRevisionsException() : void {
    $this->setEntityTypes(['node']);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid entity type.');
    $this->getSut()->deleteRevisions('node', ['1']);
  }

  /**
   * Make sure an exception is thrown when the entity type does not exist.
   */
  public function testGetPreviousRevisionsException() : void {
    $this->setEntityTypes(['node']);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid entity type.');
    $this->getSut()->getRevisions('node', '1');
  }

  /**
   * Tests whether the entity type is supported or not.
   */
  public function testEntityTypeIsSupported() : void {
    $this->assertFalse($this->getSut()->entityTypeIsSupported('remote_entity_test'));
    $this->setEntityTypes(['remote_entity_test']);
    $this->assertTrue($this->getSut()->entityTypeIsSupported('remote_entity_test'));
  }

  /**
   * Tests processing of non-existent entities.
   */
  public function testNonExistentEntity() : void {
    $this->setEntityTypes(['remote_entity_test']);
    $this->assertEmpty($this->getSut()->getRevisions('remote_entity_test', 1));
    $this->getSut()->deleteRevisions('remote_entity_test', [1, 2, 3]);
  }

  /**
   * Asserts number of items in a queue.
   *
   * @param int $expected
   *   The expected number of items in queue.
   */
  private function assertQueueItems(int $expected) : void {
    /** @var \Drupal\Core\Queue\QueueInterface $queue */
    $queue = $this->container->get('queue')->get('helfi_api_base_revision');

    $this->assertEquals($expected, $queue->numberOfItems());
  }

  /**
   * Tests revision deletion.
   */
  public function testRevision() : void {
    $this->config('helfi_api_base.delete_revisions')
      ->set('keep', 5)
      ->save();
    $this->setEntityTypes(['remote_entity_test']);
    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('remote_entity_test');
    /** @var \Drupal\remote_entity_test\Entity\RemoteEntityTest $entity */
    $entity = $storage->create(['name' => 'test en', 'id' => 1]);
    $entity->save();

    $entity->addTranslation('fi', ['name' => 'test fi'])
      ->addTranslation('sv', ['name' => 'test sv'])
      ->save();

    $this->assertCount(0, $this->getSut()->getRevisions('remote_entity_test', $entity->id()));
    $this->assertQueueItems(0);

    for ($i = 0; $i < 10; $i++) {
      $rmt = $storage->load($entity->id());
      $this->assertInstanceOf(RemoteEntityTest::class, $rmt);
      $rmt->set('name', 'test en ' . $i);

      $rmt->getTranslation('fi')
        ->set('name', 'name fi ' . $i);

      // Skip one revision to make sure only affected translations are tracked.
      if ($i > 0) {
        $rmt->getTranslation('sv')
          ->set('name', 'name sv ' . $i);
      }

      $storage->createRevision($rmt)->save();
    }
    // Make sure items are queued on entity update.
    $this->assertQueueItems(5);

    $revisions = $this->getSut()->getRevisionsPerLanguage('remote_entity_test', $entity->id());

    // We have $i + 1 revisions (11) in total, except sv, which has 10 because
    // we skipped one revision on purpose.
    foreach (['en' => 5, 'fi' => 5, 'sv' => 4] as $langcode => $expected) {
      $this->assertCount($expected, $revisions[$langcode]);
    }
    $this->assertCount(5, $this->getSut()->getRevisions('remote_entity_test', $entity->id()));

    // Make sure no revisions are returned when $keep exceeds the number of
    // total revisions.
    $revisions = $this->getSut()->getRevisionsPerLanguage('remote_entity_test', $entity->id(), 10);
    $this->assertCount(0, $revisions['sv']);

    // Delete previous revisions and make sure there are still 5 remaining
    // for each language.
    $this->getSut()->deleteRevisions('remote_entity_test', $this->getSut()->getRevisions('remote_entity_test', $entity->id()));

    $revisions = $this->getSut()->getRevisionsPerLanguage('remote_entity_test', $entity->id(), 0);

    foreach (['en' => 5, 'fi' => 5, 'sv' => 5] as $langcode => $expected) {
      $this->assertCount($expected, $revisions[$langcode]);
    }
    $this->assertCount(5, $this->getSut()->getRevisions('remote_entity_test', $entity->id(), 0));
  }

  /**
   * Tests 'keep revisions' configuration.
   */
  public function testGetKeepRevisions() : void {
    $this->assertEquals(RevisionManager::KEEP_REVISIONS, $this->getSut()->getKeepRevisions());

    foreach (['', 0, NULL] as $value) {
      $this->config('helfi_api_base.delete_revisions')
        ->set('keep', $value)
        ->save();
      $this->assertEquals(RevisionManager::KEEP_REVISIONS, $this->getSut()->getKeepRevisions());
    }
    $this->config('helfi_api_base.delete_revisions')
      ->set('keep', 15)
      ->save();
    $this->assertEquals(15, $this->getSut()->getKeepRevisions());
  }

}

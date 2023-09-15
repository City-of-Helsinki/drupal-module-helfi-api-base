<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Revision;

use Drupal\helfi_api_base\Entity\Revision\RevisionManager;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\LanguageManagerTrait;

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
    'helfi_api_base',
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
  protected function setUp() : void {
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
  private function setEntityTypes(array $entityTypes) : void {
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
   * Tests revision deletion.
   */
  public function testRevision() : void {
    $this->setEntityTypes(['remote_entity_test']);
    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('remote_entity_test');
    /** @var \Drupal\remote_entity_test\Entity\RemoteEntityTest $entity */
    $entity = $storage->create(['name' => 'test en', 'id' => 1]);
    $entity->save();

    $entity->addTranslation('fi', ['name' => 'test fi'])
      ->addTranslation('sv', ['name' => 'test sv'])
      ->save();

    for ($i = 0; $i < 10; $i++) {
      $rmt = $storage->load($entity->id());
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
    $revisions = $this->getSut()->getRevisionsPerLanguage('remote_entity_test', $entity->id());

    // We have $i + 1 revisions (11) in total, except sv, which has 10 because
    // we skipped one revision on purpose.
    foreach (['en' => 6, 'fi' => 6, 'sv' => 5] as $langcode => $expected) {
      $this->assertCount($expected, $revisions[$langcode]);
    }
    $this->assertCount(6, $this->getSut()->getRevisions('remote_entity_test', $entity->id()));

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

}

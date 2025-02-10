<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Base class for remote entities.
 */
abstract class RemoteEntityBase extends ContentEntityBase implements RemoteEntityInterface {

  /**
   * The maximum sync attempts.
   *
   * This determines how many times we attempt to sync the
   * given entity before deleting it.
   *
   * @see \Drupal\helfi_api_base\EventSubscriber\MigrationSubscriber::onPostImport().
   *
   * @var int
   */
  public const MAX_SYNC_ATTEMPTS = 2;

  /**
   * Whether to reset sync attempts.
   *
   * @var bool
   */
  protected bool $resetSyncAttempts = TRUE;

  /**
   * Gets the migration name.
   *
   * @return string|null
   *   The migration name.
   */
  public static function getMigration() : ? string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // Make sure entity id is set manually before saving.
    if (!$this->id()) {
      throw new \InvalidArgumentException('ID must be set before saving the entity.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    if ($this->resetSyncAttempts) {
      $this->resetSyncAttempts();
    }

    return parent::save();
  }

  /**
   * {@inheritdoc}
   */
  public function delete(bool $forceDelete = FALSE) : void {
    $hasDeleteForm = $this->hasLinkTemplate('delete-form');

    // Disable deleting entities to prevent accidental automatic deletions if
    // entity type does not define delete form.
    if (!$hasDeleteForm && !$forceDelete) {
      \Drupal::logger('helfi_api_base')
        ->notice('Prevented deleting entity @type with ID @id. Deleting Remote entities without "delete-form" is disabled. This can be overridden by calling ::delete() with forceDelete = TRUE.',
          [
            '@id' => $this->id(),
            '@type' => $this->getEntityTypeId(),
          ]);
      return;
    }
    parent::delete();
  }

  /**
   * Increments sync attempts counter.
   *
   * @param int $increment
   *   Amount to increment.
   *
   * @return $this
   *   The self.
   */
  public function incrementSyncAttempts(int $increment = 1) : self {
    // Never reset sync attempts on save if we increment sync attempts.
    $this->resetSyncAttempts = FALSE;

    $this->set('sync_attempts', $this->getSyncAttempts() + $increment);
    return $this;
  }

  /**
   * Resets the sync attempts counter.
   *
   * @return $this
   *   The self.
   */
  public function resetSyncAttempts() : self {
    $this->set('sync_attempts', 0);

    return $this;
  }

  /**
   * Gets the sync attempts counter.
   *
   * @return int
   *   The sync attempts.
   */
  public function getSyncAttempts() : int {
    return (int) $this->get('sync_attempts')->value ?? 0;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // We use external id as entity id.
    $fields[$entity_type->getKey('id')] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('ID'))
      ->setSettings([
        'is_ascii' => TRUE,
      ])
      ->setReadOnly(TRUE);

    $fields['sync_attempts'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Sync attempts'))
      ->setReadOnly(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Authored on'))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setTranslatable(TRUE);

    return $fields;
  }

}

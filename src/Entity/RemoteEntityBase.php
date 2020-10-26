<?php

declare(strict_types = 1);

namespace Drupal\helfi_api\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Base class for remote entities.
 */
abstract class RemoteEntityBase extends ContentEntityBase {

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
    $this->resetSyncAttempts();

    return parent::save();
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

    return $fields;
  }

}

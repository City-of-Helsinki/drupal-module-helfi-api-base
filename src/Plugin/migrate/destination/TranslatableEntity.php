<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;

/**
 * Provides a destination plugin for translatable entities.
 *
 * @MigrateDestination(
 *   id = "translatable_entity",
 *   deriver = "Drupal\helfi_api_base\Plugin\Derivative\MigrateTranslatableEntity"
 * )
 */
class TranslatableEntity extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntity(Row $row, array $old_destination_id_values) {
    // Deal with translations that are yielded as separate objects by source
    // plugin.
    if (!$langcode = $row->getSourceProperty('language')) {
      throw new MigrateException('Missing "langcode" source property.');
    }

    $entityId = reset($old_destination_id_values) ?: $this->getEntityId($row);

    if (empty($entityId) || (!$entity = $this->storage->load($entityId))) {
      // Attempt to ensure we always have a bundle.
      if ($bundle = $this->getBundle($row)) {
        $row->setDestinationProperty($this->getKey('bundle'), $bundle);
      }
      $row->setDestinationProperty($this->getKey('langcode'), $langcode);

      // Stubs might need some required fields filled in.
      if ($row->isStub()) {
        $this->processStubRow($row);
      }
      $row = $this->onEntityCreate($langcode, $row);

      $entity = $this->storage->create($row->getDestination());
      $entity->enforceIsNew();
    }

    if ($entity->hasTranslation($langcode)) {
      // Update existing translation.
      return $this->updateEntity($entity->getTranslation($langcode), $row);
    }
    // Stubs might need some required fields filled in.
    if ($row->isStub()) {
      $this->processStubRow($row);
    }
    $row = $this->onTranslationCreate($langcode, $row);
    return $entity->addTranslation($langcode, $row->getDestination());
  }

  /**
   * {@inheritdoc}
   */
  public function updateEntity(EntityInterface $entity, Row $row) {
    $entity = parent::updateEntity($entity, $row);
    // Always delete on rollback, even if it's "default" translation.
    $this->setRollbackAction($row->getIdMap(), MigrateIdMapInterface::ROLLBACK_DELETE);

    return $entity;
  }

  /**
   * Populates default values.
   *
   * @param \Drupal\migrate\Row $row
   *   The row.
   */
  protected function populateDefaultValues(Row $row) : void {
    $defaultValues = $this->configuration['default_values'] ?? [];
    // Set default values for entity when we're creating the entity
    // for the first time. These are not supposed to be overridden by
    // migrate.
    foreach ($defaultValues ?? [] as $key => $value) {
      $row->setDestinationProperty($key, $value);
    }
  }

  /**
   * Callback when creating a new translation.
   *
   * @param string $langcode
   *   The langcode.
   * @param \Drupal\migrate\Row $row
   *   The row.
   *
   * @return \Drupal\migrate\Row
   *   The modified row.
   */
  protected function onTranslationCreate(string $langcode, Row $row) : Row {
    $this->populateDefaultValues($row);
    return $row;
  }

  /**
   * Callback when creating a new entity.
   *
   * @param string $langcode
   *   The langcode.
   * @param \Drupal\migrate\Row $row
   *   The row.
   *
   * @return \Drupal\migrate\Row
   *   The modified row.
   */
  protected function onEntityCreate(string $langcode, Row $row) : Row {
    $this->populateDefaultValues($row);
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() : array {
    return ['id' => ['type' => 'string']];
  }

  /**
   * {@inheritdoc}
   */
  public function rollback(array $destination_identifier) : void {
    // Delete the specified entity from Drupal if it exists.
    if (!$entity = $this->storage->load(reset($destination_identifier))) {
      return;
    }
    $entity instanceof RemoteEntityBase ? $entity->delete(TRUE) : $entity->delete();
  }

}

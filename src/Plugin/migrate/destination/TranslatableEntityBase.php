<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a destination plugin for translatable entities.
 */
abstract class TranslatableEntityBase extends EntityContentBase {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration = NULL
  ) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition, $migration);
    $instance->languageManager = $container->get('language_manager');

    return $instance;
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
   * Gets the translated entity for given langcode and row.
   *
   * @param string $langcode
   *   The language code.
   * @param \Drupal\migrate\Row $row
   *   The row.
   * @param array $old_destination_id_values
   *   The old destination id values.
   *
   * @return \Drupal\helfi_api_base\Entity\RemoteEntityBase
   *   The entity.
   */
  protected function getTranslatedEntity(string $langcode, Row $row, array $old_destination_id_values) : RemoteEntityBase {
    $default_langcode = $row->getSourceProperty('default_langcode') === TRUE;

    if ($default_langcode) {
      $row->setDestinationProperty('langcode', $langcode);
    }
    $entity_id = reset($old_destination_id_values) ?: $this->getEntityId($row);

    if (!empty($entity_id) && ($entity = $this->storage->load($entity_id))) {
      // Update values only when we're dealing with the original translation so
      // we don't accidentally override the default translation.
      if ($default_langcode) {
        $entity = $this->updateEntity($entity, $row) ?: $entity;
      }
    }
    else {
      // Attempt to ensure we always have a bundle.
      if ($bundle = $this->getBundle($row)) {
        $row->setDestinationProperty($this->getKey('bundle'), $bundle);
      }

      // Stubs might need some required fields filled in.
      if ($row->isStub()) {
        $this->processStubRow($row);
      }
      $entity = $this->storage->create($row->getDestination());
      $entity->enforceIsNew();
    }

    if ($entity->hasTranslation($langcode)) {
      // Update existing translation.
      return $this->updateEntity($entity->getTranslation($langcode), $row);
    }
    else {
      // Stubs might need some required fields filled in.
      if ($row->isStub()) {
        $this->processStubRow($row);
      }
      return $entity->addTranslation($langcode, $row->getDestination());
    }
  }

  /**
   * Gets the entity for field translated entities.
   *
   * Deals with entities such as:
   * @code
   * [
   *   'name' => [
   *     'fi' => 'Field name in finnish',
   *     'sv' => 'Field name in swedish',
   *   ]
   * ]
   * ... or
   * [
   *   'name_fi' => 'Field name in finnish',
   *   'name_sv' => 'Field name in swedish',
   * ]
   * @endcode
   *
   * @param \Drupal\migrate\Row $row
   *   The row.
   * @param array $old_destination_id_values
   *   The old destination id values.
   *
   * @return \Drupal\helfi_api_base\Entity\RemoteEntityBase
   *   The entity.
   */
  protected function getFieldTranslationEntity(Row $row, array $old_destination_id_values) : RemoteEntityBase {
    /** @var \Drupal\helfi_api_base\Entity\RemoteEntityBase $entity */
    $entity = parent::getEntity($row, $old_destination_id_values);
    $default_language = $this->languageManager->getDefaultLanguage();
    $row = $this->populateFieldTranslations($default_language, $row);

    $languages = $this->languageManager->getLanguages();
    unset($languages[$default_language->getId()]);

    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $languageRow = $this->populateFieldTranslations($language, $row);

      if ($entity->hasTranslation($langcode)) {
        // Update existing translation.
        $translation = $entity->getTranslation($langcode);
        $this->updateEntity($translation, $languageRow);
      }
      else {
        // Stubs might need some required fields filled in.
        if ($languageRow->isStub()) {
          $this->processStubRow($languageRow);
        }
        $translation = $entity->addTranslation($langcode, $languageRow->getDestination());
        $translation->enforceIsNew();
      }
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntity(Row $row, array $old_destination_id_values) {
    // Deal with translations that are yielded as separate objects by source
    // plugin.
    if ($langcode = $row->getSourceProperty('language')) {
      return $this->getTranslatedEntity($langcode, $row, $old_destination_id_values);
    }
    // Deal with translations that have translated fields, such as:
    // ['name' => ['fi' => 'Name in finnish', 'sv' => 'Name in swedish'].
    // or ['name_fi' => 'Name in finnish', 'name_sv' => 'Name in swedish'].
    return $this->getFieldTranslationEntity($row, $old_destination_id_values);
  }

  /**
   * Gets the translatable source fields.
   *
   * Defined as remote field name => local field name:
   *
   * @code
   * [
   *   'name => 'field_name',
   *   'www' =>  'field_url',
   * ]
   * @endcode
   * Language code will be appended to remote field automatically. For
   * example the field `name` will become name_fi, name_en etc.
   *
   * @return string[]
   *   An array of source fields.
   */
  protected function getTranslatableFields() : array {
    return [];
  }

  /**
   * Populates the row object values.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language.
   * @param \Drupal\migrate\Row $row
   *   The row.
   *
   * @return \Drupal\migrate\Row
   *   The row.
   */
  protected function populateFieldTranslations(LanguageInterface $language, Row $row) : Row {
    $langcode = $language->getId();

    if (!$row->get('langcode')) {
      $row->setDestinationProperty('langcode', $langcode);
    }

    foreach ($this->getTranslatableFields() as $remote => $local) {
      // Attempt to read fields in given order:
      // @code
      // - name_fi
      // - name
      // - name_{langcode}
      // @endcode
      $fields = [
        sprintf('%s_fi', $remote),
        $remote,
        sprintf('%s_%s', $remote, $langcode),
      ];

      $value = NULL;
      foreach ($fields as $field) {
        if (!$row->hasSourceProperty($field)) {
          continue;
        }
        $value = $row->getSourceProperty($field);
      }

      if (!$value) {
        continue;
      }

      // Deal with nested translated fields (an array with langcode => value).
      if (is_array($value)) {
        // Attempt to read value in current language, fallback to first value.
        $value = $value[$langcode] ?? reset($value);
      }
      $row->setDestinationProperty($local, $value);
    }

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return ['id' => ['type' => 'string']];
  }

}

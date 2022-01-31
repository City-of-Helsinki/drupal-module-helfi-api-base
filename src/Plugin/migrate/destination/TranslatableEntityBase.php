<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\migrate\destination;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a destination base plugin for translatable entities.
 *
 * @deprecated in 1.3.3 and is removed from 2.0.0.
 */
abstract class TranslatableEntityBase extends TranslatableEntity {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   */
  protected function getEntity(Row $row, array $old_destination_id_values) {
    // Deal with translations that have translated fields, such as:
    // ['name' => ['fi' => 'Name in finnish', 'sv' => 'Name in swedish'].
    // or ['name_fi' => 'Name in finnish', 'name_sv' => 'Name in swedish'].
    return $this->getFieldTranslationEntity($row, $old_destination_id_values);
  }

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

    $default_langcode = $this->languageManager->getDefaultLanguage()->getId();
    $row = $this->populateFieldTranslations($default_langcode, $row);

    $languages = $this->languageManager->getLanguages();
    unset($languages[$default_langcode]);

    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $languageRow = $this->populateFieldTranslations($langcode, $row);

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
   * Populates the row object values.
   *
   * @param string $langcode
   *   The langcode.
   * @param \Drupal\migrate\Row $row
   *   The row.
   *
   * @return \Drupal\migrate\Row
   *   The row.
   */
  protected function populateFieldTranslations(string $langcode, Row $row) : Row {
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

}

<?php

declare(strict_types = 1);

namespace Drupal\remote_entity_test\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;

/**
 * Defines the entity entity class.
 *
 * @ContentEntityType(
 *   id = "remote_entity_test",
 *   label = @Translation("Remote entity test"),
 *   label_collection = @Translation("Remote entity test"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\helfi_trp\Entity\Listing\UnitListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\helfi_api_base\Entity\Access\RemoteEntityAccess",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\helfi_api_base\Entity\Routing\EntityRouteProvider",
 *     }
 *   },
 *   base_table = "remote_entity_test",
 *   data_table = "remote_entity_test_field_data",
 *   admin_permission = "administer remote entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "langcode" = "langcode",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {},
 *   field_ui_base_route = "trp_unit.settings"
 * )
 */
final class RemoteEntityTest extends RemoteEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setDefaultValue('')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ]);

    return $fields;
  }

}

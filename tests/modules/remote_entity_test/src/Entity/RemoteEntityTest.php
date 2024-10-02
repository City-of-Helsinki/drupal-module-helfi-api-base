<?php

declare(strict_types=1);

namespace Drupal\remote_entity_test\Entity;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the remote entity test class.
 *
 * @ContentEntityType(
 *   id = "remote_entity_test",
 *   label = @Translation("Remote entity test"),
 *   label_collection = @Translation("Remote entity test"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\helfi_api_base\Entity\Access\RemoteEntityAccess",
 *     "form" = {
 *       "default" = "Drupal\remote_entity_test\Entity\RemoteEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\helfi_api_base\Entity\Routing\EntityRouteProvider",
 *     }
 *   },
 *   base_table = "rmt",
 *   data_table = "rmt_field_data",
 *   admin_permission = "administer remote entities",
 *   translatable = TRUE,
 *   revision_table = "rmt_revision",
 *   revision_data_table = "rmt_field_revision",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "langcode" = "langcode",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "published" = "content_translation_status",
 *     "owner" = "content_translation_uid",
 *   },
 *   links = {
 *     "canonical" = "/rmt/{remote_entity_test}",
 *     "edit-form" = "/admin/content/rmt/{remote_entity_test}/edit",
 *     "delete-form" = "/rmt/{remote_entity_test}/delete",
 *     "collection" = "/admin/content/remote-entity-test",
 *   },
 * )
 */
final class RemoteEntityTest extends RemoteEntityBase implements EntityPublishedInterface, EntityOwnerInterface, RevisionableInterface, RevisionLogInterface {

  use EntityPublishedTrait;
  use EntityOwnerTrait;
  use RevisionLogEntityTrait;

  /**
   * {@inheritdoc}
   */
  public const MAX_SYNC_ATTEMPTS = 5;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    $fields += static::revisionLogBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ]);

    $fields['menu_link'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Menu link'))
      ->setSettings([
        'target_type' => 'menu_link_content',
      ])
      ->setTranslatable(TRUE);

    return $fields;
  }

}

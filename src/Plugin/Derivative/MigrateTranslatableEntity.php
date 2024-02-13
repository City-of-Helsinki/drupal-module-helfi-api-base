<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\Derivative;

use Drupal\helfi_api_base\Plugin\migrate\destination\TranslatableEntity;
use Drupal\migrate\Plugin\Derivative\MigrateEntity;

/**
 * Deriver for translatable_entity:ENTITY_TYPE entity migrations.
 */
class MigrateTranslatableEntity extends MigrateEntity {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) : array {
    foreach ($this->entityDefinitions as $entity_type => $entity_info) {
      $this->derivatives[$entity_type] = [
        'id' => "translatable_entity:$entity_type",
        'class' => TranslatableEntity::class,
        'requirements_met' => 1,
        'provider' => $entity_info->getProvider(),
      ];
    }
    return $this->derivatives;
  }

}

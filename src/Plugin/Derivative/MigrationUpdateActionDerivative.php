<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an action to update singular migration.
 */
class MigrationUpdateActionDerivative extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $instance = new static();
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    if (empty($this->derivatives)) {
      $definitions = [];

      $entity_types = array_filter($this->entityTypeManager->getDefinitions(), function (EntityTypeInterface $entityType) {
        if (!$entityType->entityClassImplements(RemoteEntityBase::class)) {
          return FALSE;
        }
        return $entityType->getClass()::getMigration() ?? FALSE;
      });

      foreach ($entity_types as $entity_type_id => $entity_type) {
        $definition = $base_plugin_definition;
        $definition['type'] = $entity_type_id;
        $definition['label'] = sprintf('%s %s', $base_plugin_definition['action_label'], $entity_type->getSingularLabel());
        $definitions[$entity_type_id] = $definition;
      }
      $this->derivatives = $definitions;
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}

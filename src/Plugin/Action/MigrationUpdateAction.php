<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;
use Drupal\helfi_api_base\MigrateTrait;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Updates the remote entity using associated migration.
 *
 * @Action(
 *   id = "remote_entity:migration_update",
 *   action_label = @Translation("Remote entity - Migration update"),
 *   deriver = "Drupal\helfi_api_base\Plugin\Derivative\MigrationUpdateActionDerivative",
 * )
 */
class MigrationUpdateAction extends ActionBase implements ContainerFactoryPluginInterface {

  use MigrateTrait;

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected MigrationPluginManagerInterface $migrationPluginManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    $instance = new static($configuration, $plugin_definition, $plugin_definition);
    $instance->migrationPluginManager = $container->get('plugin.manager.migration');
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * The migration.
   *
   * @param \Drupal\helfi_api_base\Entity\RemoteEntityBase $entity
   *   The entity.
   *
   * @return \Drupal\migrate\Plugin\MigrationInterface
   *   The migration.
   */
  protected function getMigration(RemoteEntityBase $entity) : MigrationInterface {
    $definition = $this->entityTypeManager
      ->getDefinition($this->getPluginDefinition()['type'])
      ->getClass()::getMigration();

    return $this->migrationPluginManager->createInstance($definition, [
      'entity_ids' => [$entity->id()],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity instanceof RemoteEntityBase) {
      throw new \InvalidArgumentException('Given entity is not instanceof RemoteEntityBase.');
    }
    $this->setIsPartialMigrate(TRUE);
    $migration = $this->getMigration($entity);

    $updateMap = [];
    foreach ($migration->getSourcePlugin()->getIds() as $key => $values) {
      $entityKey = $key;

      if (!$entity->hasField($key)) {
        if (!isset($values['entity_key'])) {
          // @codingStandardsIgnoreLine
          @trigger_error("Calling MigrateUpdateAction::execute() without definining 'entity_key' in source plugin's ::getIds() method is deprecated in helfi_api_base:1.3.0 and is removed in helfi_api_base:2.0.0.", E_USER_DEPRECATED);
          continue;
        }
        $entityKey = $values['entity_key'];
      }
      $value = $entity->get($entityKey)
        ->first()
        ->getValue();

      $updateMap[$key] = reset($value);
    }
    $migration->getIdMap()->setUpdate($updateMap);

    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * {@inheritdoc}
   */
  public function access(
    $object,
    AccountInterface $account = NULL,
    $return_as_object = FALSE
  ) {
    /** @var \Drupal\helfi_api_base\Entity\RemoteEntityBase $object */
    $access = $object->access('update', NULL, TRUE);

    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $module_name = $this->entityTypeManager
      ->getDefinition($this->getPluginDefinition()['type'])
      ->getProvider();
    return ['module' => [$module_name]];
  }

}

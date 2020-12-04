<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate_tools\MigrateExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an action base to run actions on migration entities.
 */
abstract class MigrateActionBase extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The default permission used to check access.
   *
   * @var string
   */
  protected string $accessPermission = 'edit remote entities';

  /**
   * The migration pluginm anager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected MigrationPluginManagerInterface $migrationPluginManager;

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

    return $instance;
  }

  /**
   * The migration.
   *
   * @return \Drupal\migrate\Plugin\MigrationInterface
   *   The migration.
   */
  abstract protected function getMigration() : MigrationInterface;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity instanceof RemoteEntityBase) {
      throw new \InvalidArgumentException('Given entity is not instanceof RemoteEntityBase.');
    }
    $migration = $this->getMigration();
    $migration->getIdMap()
      ->setUpdate([$entity->id()]);

    $executable = new MigrateExecutable($this->getMigration(), new MigrateMessage());
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
    $access = $object->access($this->accessPermission);

    return $return_as_object ? $access : $access->isAllowed();
  }

}

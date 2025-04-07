<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\DebugDataItem;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Attribute\DebugDataItem;
use Drupal\helfi_api_base\DebugDataItemPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the debug_data_item.
 */
#[DebugDataItem(
  id: 'maintenance_mode',
  title: new TranslatableMarkup('Maintenance mode'),
)]
final class MaintenanceMode extends DebugDataItemPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private StateInterface $state;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) : self {
    $instance = new self($configuration, $plugin_id, $plugin_definition);
    $instance->state = $container->get('state');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(): array {
    return ['maintenance_mode' => !$this->check()];
  }

  /**
   * {@inheritdoc}
   */
  public function check(): bool {
    // Maintenance mode = false indicates success.
    return !$this->state->get('system.maintenance_mode');
  }

}

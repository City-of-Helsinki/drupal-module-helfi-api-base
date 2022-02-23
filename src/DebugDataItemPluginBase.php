<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for debug_data_item plugins.
 */
abstract class DebugDataItemPluginBase extends PluginBase implements DebugDataItemInterface {

  /**
   * {@inheritdoc}
   */
  public function label() : string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

}

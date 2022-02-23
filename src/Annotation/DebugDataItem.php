<?php

namespace Drupal\helfi_api_base\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines debug_data_item annotation object.
 *
 * @Annotation
 */
class DebugDataItem extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}

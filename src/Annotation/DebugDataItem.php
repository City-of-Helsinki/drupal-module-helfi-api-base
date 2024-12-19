<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

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
  public string $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation|string
   *
   * @ingroup plugin_translatable
   */
  public Translation|string $title;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation|string
   *
   * @ingroup plugin_translatable
   */
  public Translation|string $description;

}

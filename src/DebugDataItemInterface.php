<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base;

use Drupal\Component\Plugin\DependentPluginInterface;

/**
 * Interface for debug_data_item plugins.
 */
interface DebugDataItemInterface extends DependentPluginInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label() : string;

}

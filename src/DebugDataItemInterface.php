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

  /**
   * Collects the data.
   *
   * This is used to render debug item.
   *
   * @return array
   *   The data.
   */
  public function collect() : array;

  /**
   * Converts debug item into boolean value.
   *
   * @return bool
   *   True if the debug test should pass.
   */
  public function check() : bool;

}

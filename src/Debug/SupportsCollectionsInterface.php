<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Debug;

/**
 * An interface to indicate this debug plugin supports collections.
 */
interface SupportsCollectionsInterface {

  /**
   * Collects the data.
   *
   * This is used to render debug item.
   *
   * @return array
   *   The data.
   */
  public function collect() : array;

}

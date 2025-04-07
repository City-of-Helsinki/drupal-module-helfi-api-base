<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The DebugDataItem attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class DebugDataItem extends Plugin {

  /**
   * Constructs a plugin attribute object.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $title
   *   (optional) The human-readable name of the plugin.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    string $id,
    public readonly TranslatableMarkup $title,
    ?string $deriver = NULL,
  ) {
    parent::__construct($id, $deriver);
  }

}

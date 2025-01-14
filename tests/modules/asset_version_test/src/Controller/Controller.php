<?php

declare(strict_types=1);

namespace Drupal\asset_version_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns response with test js library.
 */
final class Controller extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() : array {
    return [
      "#attached" => [
        "library" => [
          "asset_version_test/my-library",
        ],
      ],
    ];
  }

}

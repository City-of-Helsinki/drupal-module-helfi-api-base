<?php

// phpcs:ignoreFile
// @todo remove phpcs ignore once https://www.drupal.org/project/coder/issues/3283741 is fixed.

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Enum to contains all available environments.
 */
enum EnvMapping : string {

  case Local = 'local';
  case Test = 'test';
  case Stage = 'stage';
  case Prod = 'prod';

  /**
   * Gets the translated label for given environment.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translated label.
   */
  public function label() : TranslatableMarkup {
    return match ($this) {
      self::Local => new TranslatableMarkup('Local'),
      self::Test => new TranslatableMarkup('Testing'),
      self::Stage => new TranslatableMarkup('Staging'),
      self::Prod => new TranslatableMarkup('Production'),
    };
  }

}

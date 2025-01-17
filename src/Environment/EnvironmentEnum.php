<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Enum to contains all available environments.
 */
enum EnvironmentEnum: string {

  case Local = 'local';
  case Dev = 'dev';
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
      self::Dev => new TranslatableMarkup('Development'),
      self::Test => new TranslatableMarkup('Testing'),
      self::Stage => new TranslatableMarkup('Staging'),
      self::Prod => new TranslatableMarkup('Production'),
    };
  }

}

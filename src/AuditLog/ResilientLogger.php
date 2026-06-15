<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\AuditLog;

use Drupal\helfi_api_base\Environment\EnvironmentResolverInterface;
use ResilientLogger\ResilientLogger as ResilientLoggerBase;
use Drupal\Core\Site\Settings;

/**
 * Implements resilient logger.
 *
 * @phpstan-import-type ResilientLoggerOptions from \ResilientLogger\Types
 */
class ResilientLogger extends ResilientLoggerBase {

  /**
   * Create from settings.
   */
  public static function createFromSettings(Settings $settings, EnvironmentResolverInterface $environmentResolver): ResilientLoggerBase {
    /** @var ResilientLoggerOptions $options */
    $options = $settings->get('resilient_logger', []);

    try {
      $options['environment'] = $environmentResolver
        ->getActiveEnvironment()
        ->getEnvironmentName();

      $options['origin'] = $environmentResolver
        ->getActiveProject()
        ->getName();
    }
    catch (\InvalidArgumentException) {
    }

    return ResilientLoggerBase::create($options);
  }

}

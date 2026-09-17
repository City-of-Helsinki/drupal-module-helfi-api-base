<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Drush\Commands;

use Drupal\Core\Site\Settings;
use Drupal\helfi_api_base\Environment\EnvironmentResolverInterface;
use Drush\Commands\AutowireTrait;
use ResilientLogger\ResilientLogger;
use Symfony\Component\Console\Command\Command;

/**
 * A base class for audit log drush commands.
 *
 * @phpstan-import-type ResilientLoggerOptions from \ResilientLogger\Types as LoggerOptions
 */
abstract class AuditLogCommandBase extends Command {

  use AutowireTrait;

  public function __construct(
    private readonly Settings $settings,
    private readonly EnvironmentResolverInterface $environmentResolver,
  ) {
    parent::__construct();
  }

  /**
   * Create from settings.
   */
  protected function getResilientLogger() : ?ResilientLogger {
    /** @var LoggerOptions $options */
    $options = $this->settings->get('resilient_logger', []);

    if (!$options) {
      return NULL;
    }

    try {
      $options['environment'] = $this->environmentResolver
        ->getActiveEnvironment()
        ->getEnvironmentName();

      $options['origin'] = $this->environmentResolver
        ->getActiveProject()
        ->getName();
    }
    catch (\InvalidArgumentException) {
    }

    return ResilientLogger::create($options);
  }

}

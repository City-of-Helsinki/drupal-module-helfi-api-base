<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Drush\Commands;

use Drush\Commands\AutowireTrait;
use ResilientLogger\ResilientLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A drush command to clear already sent audit log entries.
 *
 * @see \Drupal\helfi_api_base\HelfiApiBaseServiceProvider::register()
 */
#[AsCommand(
  name: 'helfi:audit-log:clear-sent-entries',
  description: 'Clears sent audit log entries that are past the retention period.',
)]
final class AuditLogClearSentEntriesCommand extends Command {

  use AutowireTrait;

  public function __construct(
    private readonly ?ResilientLogger $resilientLogger = NULL,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) : int {
    $io = new SymfonyStyle($input, $output);

    if (!$this->resilientLogger) {
      $io->error('The audit log is not configured. Nothing to clear.');

      return self::FAILURE;
    }

    $this->resilientLogger->clearSentEntries();

    $io->writeln('Cleared sent audit log entries.');

    return self::SUCCESS;
  }

}

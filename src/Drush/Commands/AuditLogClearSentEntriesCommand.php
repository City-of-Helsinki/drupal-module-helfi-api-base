<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Drush\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A drush command to clear already sent audit log entries.
 */
#[AsCommand(
  name: 'helfi:audit-log:clear-sent-entries',
  description: 'Clears sent audit log entries that are past the retention period.',
)]
final class AuditLogClearSentEntriesCommand extends AuditLogCommandBase {

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) : int {
    $io = new SymfonyStyle($input, $output);

    if (!$logger = $this->getResilientLogger()) {
      $io->note('The audit log is not configured. Nothing to clear.');

      return self::SUCCESS;
    }

    $logger->clearSentEntries();

    $io->writeln('Cleared sent audit log entries.');

    return self::SUCCESS;
  }

}

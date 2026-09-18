<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Drush\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A drush command to submit unsent audit log entries.
 */
#[AsCommand(
  name: 'helfi:audit-log:submit-unsent-entries',
  description: 'Submits unsent audit log entries to the configured targets.',
)]
final class AuditLogSubmitUnsentEntriesCommand extends AuditLogCommandBase {

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) : int {
    $io = new SymfonyStyle($input, $output);

    if (!$logger = $this->getResilientLogger()) {
      $io->note('The audit log is not configured. Nothing to submit.');

      return self::SUCCESS;
    }

    $results = $logger->submitUnsentEntries();

    $failed = count(array_filter($results, static fn (bool $success) => !$success));
    $submitted = count($results) - $failed;

    $io->writeln(sprintf('Submitted %d audit log entries.', $submitted));

    if ($failed > 0) {
      $io->error(sprintf('Failed to submit %d audit log entries.', $failed));

      return self::FAILURE;
    }

    return self::SUCCESS;
  }

}

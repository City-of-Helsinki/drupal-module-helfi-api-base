<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Package;

use Symfony\Component\Process\Process;

/**
 * Process for `composer outdated` command.
 */
class ComposerOutdatedProcess {

  /**
   * Runs `composer outdated`.
   *
   * @return array
   *   Decoded JSON from `composer outdated`.
   *
   * @throws \Symfony\Component\Process\Exception\ProcessFailedException
   */
  public function run($workingDir): array {
    $process = new Process([
      'composer', 'outdated', '--direct', '--format=json', '--working-dir=' . $workingDir,
    ]);
    $process->mustRun();
    return json_decode($process->getOutput(), TRUE);
  }

}

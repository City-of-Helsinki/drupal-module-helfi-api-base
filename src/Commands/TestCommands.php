<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\Core\Database\Database;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Test\EnvironmentCleaner;
use Drupal\Core\Test\TestDatabase;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 */
final class TestCommands extends DrushCommands {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   */
  public function __construct(private FileSystemInterface $fileSystem) {
    parent::__construct();
  }

  /**
   * A command to clean up old test results.
   *
   * @command helfi:test:clean-environment
   */
  public function cleanEnvironment() {
    $cleaner = new EnvironmentCleaner(
      DRUPAL_ROOT,
      Database::getConnection(),
      TestDatabase::getConnection(),
      $this->output,
      $this->fileSystem
    );
    $cleaner->cleanEnvironment(FALSE);
  }

}

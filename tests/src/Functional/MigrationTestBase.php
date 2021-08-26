<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\migrate\MigrateMessageInterface;
use Drupal\Tests\helfi_api_base\Traits\MigrationTestTrait;

/**
 * Base class for multilingual migration tests.
 */
abstract class MigrationTestBase extends BrowserTestBase implements MigrateMessageInterface {

  use MigrationTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
  ];

}

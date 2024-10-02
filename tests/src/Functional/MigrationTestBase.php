<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Tests\helfi_api_base\Traits\MigrationTestTrait;
use Drupal\migrate\MigrateMessageInterface;

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

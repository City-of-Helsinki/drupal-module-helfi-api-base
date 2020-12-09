<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\Tests\BrowserTestBase;
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
    'migrate_plus',
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    foreach (['fi', 'sv'] as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
  }

}

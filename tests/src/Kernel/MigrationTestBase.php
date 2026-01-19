<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Tests\helfi_api_base\Traits\MigrationTestTrait;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\migrate\MigrateMessageInterface;

/**
 * Base class for multilingual migration tests.
 */
abstract class MigrationTestBase extends ApiKernelTestBase implements MigrateMessageInterface {

  use MigrationTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->installConfig(['language', 'content_translation']);

    foreach (['fi', 'sv'] as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
  }

}

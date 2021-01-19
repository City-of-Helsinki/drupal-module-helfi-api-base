<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\Tests\helfi_api_base\Traits\MigrationTestTrait;

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
  public function setUp() : void {
    parent::setUp();

    $this->installConfig(['language', 'content_translation']);

    foreach (['fi', 'sv'] as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
  }

}

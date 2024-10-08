<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Functional;

use Drupal\Tests\BrowserTestBase as CoreBrowserTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Base class for multilingual tests.
 */
abstract class BrowserTestBase extends CoreBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
    'content_translation',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    foreach (['fi', 'sv'] as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
    $account = $this->drupalCreateUser([
      'administer languages',
      'access administration pages',
    ]);
    $this->drupalLogin($account);

    $edit = [
      'language_interface[enabled][language-session]' => TRUE,
      'language_interface[weight][language-session]' => -12,
    ];
    $this->drupalGet('/admin/config/regional/language/detection');
    $this->submitForm($edit, 'Save settings');
    // Make sure we are not logged in.
    $this->drupalLogout();

  }

}

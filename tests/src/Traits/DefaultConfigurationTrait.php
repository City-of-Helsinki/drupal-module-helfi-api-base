<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\Core\Config\Config;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;

/**
 * Basic configuration for each test.
 */
trait DefaultConfigurationTrait {

  /**
   * The default language.
   *
   * @var string
   */
  protected string $defaultLanguage = '';

  /**
   * Gets the language object for given langcode.
   *
   * @param string $langcode
   *   The langcode.
   *
   * @return \Drupal\Core\Language\LanguageInterface
   *   The language.
   */
  protected function getLanguage(string $langcode) : LanguageInterface {
    return \Drupal::languageManager()->getLanguage($langcode);
  }

  /**
   * Wrapper for drupalGet() to always set language code.
   *
   * @param string|\Drupal\Core\Url $url
   *   The url.
   * @param string $langcode
   *   The langcode.
   * @param array $options
   *   The options.
   * @param array $headers
   *   The headers.
   */
  protected function drupalGetWithLanguage(string|Url $url, string $langcode = 'en', array $options = [], array $headers = []) : void {
    $options['language'] = $this->getLanguage($langcode);
    $this->drupalGet($url, $options, $headers);
  }

  /**
   * Set up the default configuration.
   */
  protected function setupDefaultConfiguration() : void {
    $this->defaultLanguage = $this->getDefaultLanguageConfiguration()
      ->get('selected_langcode');
    $this->setDefaultLanguage('en');
  }

  /**
   * Restores the default configuration.
   */
  protected function tearDownDefaultConfiguration() : void {
    $this->setDefaultLanguage($this->defaultLanguage);
  }

  /**
   * Gets the configuration.
   *
   * @return \Drupal\Core\Config\Config
   *   The default configuration.
   */
  protected function getDefaultLanguageConfiguration() : Config {
    return \Drupal::configFactory()
      ->getEditable('language.negotiation');
  }

  /**
   * Sets the default language.
   *
   * @param string $langcode
   *   The langcode to set as default.
   */
  protected function setDefaultLanguage(string $langcode) : void {
    $this->getDefaultLanguageConfiguration()
      ->set('selected_langcode', $langcode)
      ->save();
  }

}

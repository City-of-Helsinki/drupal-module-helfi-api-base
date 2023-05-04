<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Language;

use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Resolves default languages and fallbacks.
 */
final class DefaultLanguageResolver {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Constructs a new instance.
   *
   * @param array $defaultLanguages
   *   Default languages.
   * @param string $fallbackLanguage
   *   Fallback language.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(private array $defaultLanguages, private string $fallbackLanguage, LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * Gets an array of languages with standard support.
   *
   * These can be configured by overriding the
   * 'helfi_api_base.default_languages' parameter in services.yml file.
   *
   * @return array
   *   The default languages.
   */
  public function getDefaultLanguages() : array {
    return $this->defaultLanguages;
  }

  /**
   * Gets the fallback language code.
   *
   * Non-default languages use this for certain elements.
   * Can be configured by overriding the
   * 'helfi_api_base.fallback_language' parameter in servies.yml  file.
   *
   * @return string
   *   The fallback language.
   */
  public function getFallbackLanguage(): string {
    return $this->fallbackLanguage;
  }

  /**
   * Check if current or specific language is considered not fully supported.
   *
   * Does not account for language being actually in use.
   *
   * @param string|null $langcode
   *   Langcode to check. Defaults to current language.
   *
   * @return bool
   *   If language is considered alternative and not fully supported.
   */
  public function isAltLanguage(string $langcode = NULL): bool {
    if (!$langcode) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    return !in_array($langcode, $this->defaultLanguages);
  }

  /**
   * Get current or fallback langcode.
   *
   * @return string
   *   Current or fallback language ID if current doesn't have full support.
   */
  public function getCurrentOrFallbackLanguage(): string {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    if ($this->isAltLanguage($langcode)) {
      return $this->getFallbackLanguage();
    }
    return $langcode;
  }

  /**
   * Gets lang, dir and other attributes for fallback language.
   *
   * @return array
   *   Array with attributes.
   */
  public function getFallbackLangAttributes(): array {
    $language = $this->languageManager->getLanguage($this->fallbackLanguage);

    return [
      'lang' => $language->getId(),
      'dir' => $language->getDirection(),
    ];
  }

  /**
   * Gets lang, dir and other attributes for fallback elements.
   *
   * @return array
   *   Array with attributes.
   */
  public function getCurrentLangAttributes(): array {
    return [
      'lang' => $this->languageManager->getCurrentLanguage()->getId(),
      'dir' => $this->languageManager->getCurrentLanguage()->getDirection(),
    ];
  }

}

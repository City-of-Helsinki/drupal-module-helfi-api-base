<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Language;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Resolves default languages and fallbacks.
 */
final class DefaultLanguageResolver {

  /**
   * Constructs a new instance.
   *
   * @param array $defaultLanguages
   *   Default languages.
   * @param string $fallbackLanguage
   *   Fallback language.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager.
   */
  public function __construct(
    #[Autowire('%helfi_api_base.default_languages%')] private readonly array $defaultLanguages,
    #[Autowire('%helfi_api_base.fallback_language%')] private readonly string $fallbackLanguage,
    private readonly LanguageManagerInterface $languageManager,
  ) {
  }

  /**
   * Gets an array of language IDs with standard support.
   *
   * These can be configured by overriding the
   * 'helfi_api_base.default_languages' parameter in services.yml file.
   *
   * @return array
   *   The default languages IDs.
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
   *   The fallback language ID.
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
   * @param string $type
   *   (optional) The language type.
   *
   * @return string
   *   Current or fallback language ID if current doesn't have full support.
   */
  public function getCurrentOrFallbackLanguage(string $type = LanguageInterface::TYPE_INTERFACE): string {
    $langcode = $this->languageManager->getCurrentLanguage($type)->getId();
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

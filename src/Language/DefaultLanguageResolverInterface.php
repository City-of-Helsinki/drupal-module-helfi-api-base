<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Language;

use Drupal\Core\Language\LanguageInterface;

/**
 * Resolves default languages and fallbacks.
 */
interface DefaultLanguageResolverInterface {

  /**
   * Gets an array of language IDs with standard support.
   *
   * These can be configured by overriding the
   * 'helfi_api_base.default_languages' parameter in services.yml file.
   *
   * @return array
   *   The default languages IDs.
   */
  public function getDefaultLanguages(): array;

  /**
   * Gets the fallback language code.
   *
   * Non-default languages use this for certain elements.
   * Can be configured by overriding the
   * 'helfi_api_base.fallback_language' parameter in services.yml  file.
   *
   * @return string
   *   The fallback language ID.
   */
  public function getFallbackLanguage(): string;

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
  public function isAltLanguage(?string $langcode = NULL): bool;

  /**
   * Get current or fallback langcode.
   *
   * @param string $type
   *   (optional) The language type.
   *
   * @return string
   *   Current or fallback language ID if current doesn't have full support.
   */
  public function getCurrentOrFallbackLanguage(string $type = LanguageInterface::TYPE_INTERFACE): string;

  /**
   * Gets lang, dir and other attributes for fallback language.
   *
   * @return array
   *   Array with attributes.
   */
  public function getFallbackLangAttributes(): array;

  /**
   * Gets lang, dir and other attributes for fallback elements.
   *
   * @return array
   *   Array with attributes.
   */
  public function getCurrentLangAttributes(): array;

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Language;

use Drupal\Core\Language\LanguageInterface;

/**
 * An interface to represent the default language resolver.
 */
interface DefaultLanguageResolverInterface {

  /**
   * Gets an array of language IDs with standard support.
   *
   * @return array
   *   The default languages IDs.
   */
  public function getDefaultLanguages(): array;

  /**
   * Gets the fallback language code.
   *
   * @return string
   *   The fallback language ID.
   */
  public function getFallbackLanguage(): string;

  /**
   * Check if current or specific language is considered not fully supported.
   *
   * @param string|null $langcode
   *   Langcode to check. Defaults to current language.
   *
   * @return bool
   *   If language is considered alternative and not fully supported.
   */
  public function isAltLanguage(string|null $langcode): bool;

  /**
   * Get current or fallback langcode.
   *
   * @param string $type
   *   (optional) The language type. Defaults to
   *   \Drupal\Core\Language\LanguageInterface::TYPE_INTERFACE.
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

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Language;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Resolves default languages and fallbacks.
 */
final class DefaultLanguageResolver implements DefaultLanguageResolverInterface {

  public function __construct(
    #[Autowire('%helfi_api_base.default_languages%')] private readonly array $defaultLanguages,
    #[Autowire('%helfi_api_base.fallback_language%')] private readonly string $fallbackLanguage,
    private readonly LanguageManagerInterface $languageManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultLanguages() : array {
    return $this->defaultLanguages;
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackLanguage(): string {
    return $this->fallbackLanguage;
  }

  /**
   * {@inheritdoc}
   */
  public function isAltLanguage(?string $langcode = NULL): bool {
    if (!$langcode) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    return !in_array($langcode, $this->defaultLanguages);
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentOrFallbackLanguage(string $type = LanguageInterface::TYPE_INTERFACE): string {
    $langcode = $this->languageManager->getCurrentLanguage($type)->getId();
    if ($this->isAltLanguage($langcode)) {
      return $this->getFallbackLanguage();
    }
    return $langcode;
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackLangAttributes(): array {
    $language = $this->languageManager->getLanguage($this->fallbackLanguage);

    return [
      'lang' => $language->getId(),
      'dir' => $language->getDirection(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentLangAttributes(): array {
    return [
      'lang' => $this->languageManager->getCurrentLanguage()->getId(),
      'dir' => $this->languageManager->getCurrentLanguage()->getDirection(),
    ];
  }

}

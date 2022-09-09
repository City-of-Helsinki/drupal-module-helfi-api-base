<?php

declare(strict_types = 1);

namespace Drupal\helfi_language_negotiator_test;

use Drupal\language\LanguageNegotiator as CoreLanguageNegotiator;

/**
 * Class responsible for performing language negotiation.
 */
class LanguageNegotiator extends CoreLanguageNegotiator {

  /**
   * The currently active language code.
   *
   * @var string|null
   */
  private ?string $languageCode = NULL;

  /**
   * {@inheritdoc}
   */
  public function initializeType($type) : array {
    $id = static::METHOD_ID;
    $availableLanguages = $this->languageManager->getLanguages();

    if ($this->languageCode && isset($availableLanguages[$this->languageCode])) {
      $language = $availableLanguages[$this->languageCode];
    }
    else {
      // If no other language was found use the default one.
      $language = $this->languageManager->getDefaultLanguage();
    }

    return [$id => $language];
  }

  /**
   * Sets the currently active language.
   *
   * @param null|string $languageCode
   *   The language.
   */
  public function setLanguageCode(?string $languageCode) : void {
    $this->languageCode = $languageCode;
  }

}

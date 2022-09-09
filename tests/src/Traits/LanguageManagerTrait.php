<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * A trait to manipulate currently active language.
 */
trait LanguageManagerTrait {

  /**
   * The language manager.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface|null
   */
  protected ?ConfigurableLanguageManagerInterface $languageManager = NULL;

  /**
   * Setup languages.
   *
   * @throws \Drupal\Core\Extension\Exception\UnknownExtensionException
   */
  protected function setupLanguages() : void {
    foreach (['fi', 'sv'] as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
    $this->installConfig(['language']);

    \Drupal::moduleHandler()->getModule('helfi_language_negotiator_test');
    $types = $this->config('language.types')->get('negotiation');

    foreach ($types as $key => $value) {
      $types[$key]['enabled']['language-selected'] = -1;
    }
    $this->config('language.types')->set('negotiation', $types)->save();
    // Override the language manager to make sure it's available everywhere.
    $this->container->set('language_manager', $this->languageManager());
  }

  /**
   * Initializes and gets the language manager.
   *
   * @return \Drupal\language\ConfigurableLanguageManagerInterface
   *   The language manager.
   */
  protected function languageManager() : ConfigurableLanguageManagerInterface {
    if (!$this->languageManager) {
      /** @var \Drupal\language\ConfigurableLanguageManagerInterface $languageManager */
      $this->languageManager = $this->container->get('language_manager');
      /** @var \Drupal\helfi_language_negotiator_test\LanguageNegotiator $customLanguageManager */
      $customLanguageManager = $this->container->get('helfi_language_negotiator_test.language_negotiator');
      $this->languageManager->setNegotiator($customLanguageManager);
    }
    return $this->languageManager;
  }

  /**
   * Overrides currently active language code.
   *
   * @param string $langcode
   *   The langcode.
   */
  protected function setOverrideLanguageCode(string $langcode) : void {
    $this->languageManager()->reset();
    $this->languageManager()->getNegotiator()->setLanguageCode($langcode);
  }

}

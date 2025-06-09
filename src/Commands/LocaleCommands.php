<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationManager;
use Drush\Commands\DrushCommands;

/**
 * A Drush command file.
 */
class LocaleCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\Core\StringTranslation\TranslationManager $translationManager
   *   The translation manager.
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module extension list.
   */
  public function __construct(
    protected LanguageManagerInterface $languageManager,
    protected FileSystemInterface $fileSystem,
    protected TranslationManager $translationManager,
    protected ModuleExtensionList $moduleExtensionList,
  ) {
  }

  /**
   * Gets the translation file per language.
   *
   * @param string $language
   *   The language code.
   * @param string $module
   *   The module name.
   *
   * @return object|null
   *   Translation file object or null.
   */
  private function getTranslationFile(string $language, string $module) : ?object {
    $basePath = $this->moduleExtensionList->getPath($module);
    $dir = sprintf('%s/translations/override', $basePath);

    $files = $this->fileSystem->scanDirectory($dir, "/$language.po/");

    if (empty($files)) {
      return NULL;
    }

    if ($file = reset($files)) {
      return $file?->uri ? $file : NULL;
    }
    return NULL;
  }

  /**
   * Pre-command hook to import english source strings.
   *
   * @param string $module
   *   The module name.
   *
   * @command helfi:locale-import
   */
  public function import(string $module) {
    foreach ($this->languageManager->getLanguages() as $language) {
      // Skip default language.
      if ($language->isDefault()) {
        continue;
      }

      // Continue if there are no translation files.
      if (!$file = $this->getTranslationFile($language->getId(), $module)) {
        continue;
      }

      $process = $this->processManager()->process([
        'drush',
        'locale:import',
        // Import translations as not-customized translations.
        // Let users override translations from UI translate interface.
        '--type=not-customized',
        // Override all translations.
        '--override=all',
        $language->getId(),
        $file->uri,
      ]);
      $process->run(function ($type, $output) {
        $this->io()->write($output);
      });
    }
  }

}

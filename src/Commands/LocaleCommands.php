<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\Component\Gettext\PoStreamReader;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class LocaleCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected TranslationInterface $translationManager;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected ModuleExtensionList $moduleExtensionList;

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
    LanguageManagerInterface $languageManager,
    FileSystemInterface $fileSystem,
    TranslationManager $translationManager,
    ModuleExtensionList $moduleExtensionList
  ) {
    $this->languageManager = $languageManager;
    $this->fileSystem = $fileSystem;
    $this->translationManager = $translationManager;
    $this->moduleExtensionList = $moduleExtensionList;
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

    if (!empty($files) && is_object(reset($files))) {
      return reset($files);
    }

    return NULL;
  }

  /**
   * Creates a PO stream reader instance.
   *
   * @param string $langcode
   *   The langcode.
   * @param object $file
   *   The file.
   *
   * @return \Drupal\Component\Gettext\PoStreamReader
   *   The stream reader.
   */
  private function createStreamReader(string $langcode, object $file) : PoStreamReader {
    $reader = new PoStreamReader();
    $reader->setLangcode($langcode);
    $reader->setURI($file->uri);
    $reader->open();
    return $reader;
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

      // Expose source strings (to make them translatable).
      $reader = $this->createStreamReader($language->getId(), $file);

      while ($item = $reader->readItem()) {
        $options = [
          'langcode' => $language->getId(),
        ];

        if ($context = $item->getContext()) {
          $options['context'] = $context;
        }
        $sources = $item->getSource();

        // We don't want to expose strings with plural form.
        if ($item->isPlural()) {
          continue;
        }

        if (!is_array($sources)) {
          $sources = [$sources];
        }
        foreach ($sources as $source) {
          $this->translationManager
            // @codingStandardsIgnoreLine
            ->translateString(new TranslatableMarkup($source, [], $options));
        }
      }
      $process = $this->processManager()->process([
        'drush',
        'locale:import',
        '--type=customized',
        $language->getId(),
        $file->uri,
      ]);
      $process->run(function ($type, $output) {
        $this->io()->write($output);
      });
    }
  }

}

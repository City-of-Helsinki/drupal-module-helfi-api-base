<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drush\Attributes\Command;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

/**
 * Drush command to compile twig.
 */
final class HelfiTwigCommands extends DrushCommands {

  public function __construct(
    protected TwigEnvironment $twig,
    protected ModuleHandlerInterface $moduleHandler,
    protected ThemeHandlerInterface $themeHandler,
    readonly private ExtensionList $extensionList,
  ) {
  }

  /**
   * Compile all Twig template(s).
   *
   * UHF-10063 prevent unknown function -error caused by
   * twig files in core modules' help_topics folders.
   */
  #[Command(name: 'helfi:twig:compile')]
  public function helfiTwigCompile(): void {
    require_once DRUSH_DRUPAL_CORE . "/themes/engines/twig/twig.engine";
    // Scan all enabled modules and themes.
    $modules = array_keys($this->moduleHandler->getModuleList());
    foreach ($modules as $module) {
      $searchpaths[] = $this->extensionList->getPath($module);
    }

    $themes = $this->themeHandler->listInfo();
    foreach ($themes as $theme) {
      $searchpaths[] = $theme->getPath();
    }

    // Prevent processing help_topics if the module not enabled.
    $excludes = $this->moduleHandler
      ->moduleExists('help_topics') ? 'tests' : ['tests', 'help_topics'];

    $files = Finder::create()
      ->files()
      ->name('*.html.twig')
      ->exclude($excludes)
      ->in($searchpaths);
    foreach ($files as $file) {
      $relative = Path::makeRelative($file->getRealPath(), Drush::bootstrapManager()->getRoot());
      // Loading the template ensures the compiled template is cached.
      $this->twig->load($relative);
      $this->logger()->success(dt('Compiled twig template !path', ['!path' => $relative]));
    }
  }

}

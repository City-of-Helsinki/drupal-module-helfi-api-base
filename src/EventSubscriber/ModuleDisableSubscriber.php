<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Handles deploy tasks.
 */
final class ModuleDisableSubscriber extends DeployHookEventSubscriberBase {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller
   *   The module installer service.
   */
  public function __construct(
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly ModuleInstallerInterface $moduleInstaller
  ) {
  }

  /**
   * Responds to 'helfi_api_base.post_deploy' event.
   *
   * @param \Symfony\Contracts\EventDispatcher\Event $event
   *   The event.
   */
  public function onPostDeploy(Event $event) : void {
    if ($this->moduleHandler->moduleExists('dblog')) {
      $this->moduleInstaller->uninstall(['dblog']);
    }
  }

}

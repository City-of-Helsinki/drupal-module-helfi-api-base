<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Drush\Commands;

use Drupal\helfi_api_base\Event\PostDeployEvent;
use Drupal\helfi_api_base\Event\PreDeployEvent;
use Drush\Attributes\Command;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * A drush command file.
 */
final class DeployCommands extends DrushCommands {

  use AutowireTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(
    private readonly EventDispatcherInterface $eventDispatcher,
  ) {
    parent::__construct();
  }

  /**
   * Runs reusable 'post deploy' hooks.
   *
   * These are run after drush deploy command.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:post-deploy')]
  public function postDeploy() : int {
    $this->eventDispatcher->dispatch(new PostDeployEvent());
    return DrushCommands::EXIT_SUCCESS;
  }

  /**
   * Runs reusable 'pre deploy' hooks.
   *
   * These are run before drush deploy command.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:pre-deploy')]
  public function preDeploy() : int {
    $this->eventDispatcher->dispatch(new PreDeployEvent());
    return DrushCommands::EXIT_SUCCESS;
  }

}

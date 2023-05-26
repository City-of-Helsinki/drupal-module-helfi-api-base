<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Commands;

use Drush\Attributes\Command;
use Drush\Commands\DrushCommands;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * A drush command file.
 */
final class DeployCommands extends DrushCommands {

  /**
   * Constructs a new instance.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(
    private readonly EventDispatcherInterface $eventDispatcher
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
    $this->eventDispatcher->dispatch(new Event(), 'helfi_api_base.post_deploy');
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
    $this->eventDispatcher->dispatch(new Event(), 'helfi_api_base.pre_deploy');
    return DrushCommands::EXIT_SUCCESS;
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\helfi_api_base\Event\PostDeployEvent;
use Drupal\helfi_api_base\Event\PreDeployEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Defines a base class for deploy hooks.
 */
abstract class DeployHookEventSubscriberBase implements EventSubscriberInterface {

  /**
   * Responds to 'helfi_api_base.post_deploy' event.
   *
   * @param \Symfony\Contracts\EventDispatcher\Event $event
   *   The event.
   */
  public function onPostDeploy(Event $event) : void {
  }

  /**
   * Responds to 'helfi_api_base.pre_deploy' event.
   *
   * @param \Symfony\Contracts\EventDispatcher\Event $event
   *   The event.
   */
  public function onPreDeploy(Event $event) : void {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      PostDeployEvent::class => ['onPostDeploy'],
      PreDeployEvent::class => ['onPreDeploy'],
    ];
  }

}

<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Cache;

use Drupal\helfi_api_base\EventSubscriber\DeployHookEventSubscriberBase;
use Drupal\redis\ClientFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event subscriber to flush Redis cache on deploy.
 */
final class RedisDeploySubscriber extends DeployHookEventSubscriberBase {

  public function __construct(
    #[Autowire(service: 'redis.factory')] private readonly ClientFactory $clientFactory,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function onPostDeploy(Event $event): void {
    $this->clientFactory->getClient()->flushAll();
  }

}


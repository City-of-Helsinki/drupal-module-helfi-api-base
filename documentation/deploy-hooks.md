# Deploy hooks

The module defines two Drush commands called `helfi:pre-deploy` and `helfi:post-deploy`. These are first and last things to be run during the deployment. See https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/docker/openshift/entrypoints/20-deploy.sh

## Defining deploy tasks

Add a new event subscriber:

```yml
# yourmodule/yourmodule.services.yml
  helfi_api_base.your_deploy_hook_subscriber:
    class: Drupal\helfi_api_base\EventSubscriber\YourDeployHookSubscriber
    arguments: []
    tags:
      - { name: event_subscriber }
```

```php
# yourmodule/src/EventSubscriber/YourDeployHookSubscriber.php
<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Handles deploy tasks.
 */
final class YourDeployHookSubscriber extends DeployHookEventSubscriberBase {

  /**
   * Responds to 'helfi_api_base.post_deploy' event.
   *
   * @param \Symfony\Contracts\EventDispatcher\Event $event
   *   The event.
   */
  public function onPostDeploy(Event $event) : void {
    // Do something on after deployment.
  }

  /**
   * Responds to 'helfi_api_base.pre_deploy' event.
   *
   * @param \Symfony\Contracts\EventDispatcher\Event $event
   *   The event.
   */
  public function onPreDeploy(Event $event) : void {
    // Do something before deployment.
  }

}
```

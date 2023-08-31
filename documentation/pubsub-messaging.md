# PubSub messaging

Provides an integration to [Azure's Web PubSub service](https://azure.microsoft.com/en-us/products/web-pubsub) to deliver real-time messages between instances.

## Configuration

You must define the following settings to use this feature:

```php
$config['helfi_api_base.pubsub.settings']['access_key'] = '<access-key>';
// Url to Azure's wss endpoint, usually something like: yourservicename.webpubsub.azure.com
$config['helfi_api_base.pubsub.settings']['endpoint'] = '<url to azure pubsub endpoint>';
// Hub and group must be same in all instances that talk with each other.
$config['helfi_api_base.pubsub.settings']['hub'] = '<hub>';
$config['helfi_api_base.pubsub.settings']['group'] = '<group>';
```

## Usage

### Sending a message

Use `helfi_api_base.pubsub_manager` service to send real-time messages to other instances:

```php
/** @var \Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface $service */
$service = \Drupal::service('helfi_api_base.pubsub_manager')
$data = ['random' => 'data'];
$service->sendMessage($data);
```

The `$data` variable can contain anything that can be converted into a JSON string.

### Listening to PubSub messages

Use `drush helfi:azure:pubsub-listen` Drush command to listen to an incoming messages.

The command is run until `\Drupal\helfi_api_base\Commands\PubSubCommands::MAX_MESSAGES` (500 by default) is reached and will exit with code 0 to prevent memory leaks.

Alternatively, you can listen to incoming messages using `helfi_api_base.pubsub_manager` service:

```php
/** @var \Drupal\helfi_api_base\Azure\PubSub\PubSubManagerInterface $service */
$service = \Drupal::service('helfi_api_base.pubsub_manager')
$service->receive();
```

See [Responding to PubSub messages](#responding-to-pubsub-messages) to see how to respond to incoming messages.

### Responding to PubSub messages

Create an [event subscriber](https://www.drupal.org/docs/develop/creating-modules/subscribe-to-and-dispatch-events#s-drupal-8-events) that responds to `\Drupal\helfi_api_base\Azure\PubSub\Message` events:
```php

<?php

declare(strict_types = 1);

namespace Drupal\yourmodule\EventSubscriber;

use Drupal\helfi_api_base\Azure\PubSub\PubSubMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class YourEventSubscriber implements EventSubscriberInterface {

  public function onReceive(PubSubMessage $message) : void {
    // Logic here.
    // $message->data should contain the data passed to '::sendMessage()'.
  }

  public static function getSubscribedEvents() : array {
    return [
      PubSubMessage::class => ['onReceive'],
    ];
  }

}
```

See [CacheTagInvalidatorSubscriber](/src/EventSubscriber/CacheTagInvalidatorSubscriber.php) for an example implementation.

### Testing locally

```php
# public/sites/default/local.settings.php
$config['helfi_api_base.pubsub.settings']['access_key'] = '<access-key>';
$config['helfi_api_base.pubsub.settings']['endpoint'] = '<url to azure pubsub endpoint>';
$config['helfi_api_base.pubsub.settings']['hub'] = '<hub>';
$config['helfi_api_base.pubsub.settings']['group'] = '<group>';
```

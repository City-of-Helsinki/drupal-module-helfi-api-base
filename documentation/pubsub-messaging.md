# PubSub messaging

Provides an integration to [Azure's Web PubSub service](https://azure.microsoft.com/en-us/products/web-pubsub) to deliver real-time messages between instances.

## Configuration

You must define a [JSON Vault item](/documentation/api-accounts.md#managing-external-api-credentials) to use this feature. The data field should be a JSON string containing `endpoint`, `hub`, `group` and `access_key` and optional `secondary_access_key`:

```json
{"endpoint": "<endpoint>", "hub": "<hub>", "group": "<group>", "access_key": "<access-key>", "secondary_access_key":  "<secondary-access-key>"}
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
$pubsub_account = [
  'id' => 'pubsub',
  'plugin' => 'json',
  'data' => json_encode(
    'endpoint' => '<endpoint-here>',
    'hub' => '<hub>',
    'group' => '<group>',
    'access_key' => '<access-key>',
    'secondary_access_key' => '<secondary-access-key>',
  ]),
];
$config['helfi_api_base.api_accounts']['vault'][] = $pubsub_account;
```

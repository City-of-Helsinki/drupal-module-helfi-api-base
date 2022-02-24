# Migration

## Migrate garbage collection

@todo

## Alter migration configuration

Create an event subscriber that responds to `\Drupal\helfi_api_base\Event\MigrationConfigurationEvent` event:

```yaml
# yourmodule.services.yml
yourmodule.migration_configuration_subscriber:
  class: Drupal\yourmodule\EventSubscriber\YourCustomEventSubscriber
  tags:
    - { name: event_subscriber }

```

```php
<?php
# src/EventSubscriber/YourCustomEventSubscriber.php
namespace Drupal\yourmodule\EventSubscriber;

use Drupal\helfi_api_base\Event\MigrationConfigurationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class YourCustomEventSubscriber implements EventSubscriberInterface {

  public function onMigration(MigrationConfigurationEvent $event) {
    if ($event->migration->id() !== 'tpr_unit') {
      return;
    }
    // Alter source plugin configuration here.
    $event->configuration['url'] = 'https://www.hel.fi/palvelukarttaws/rest/v4/unit/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'Drupal\helfi_api_base\Event\MigrationConfigurationEvent' => [
        ['onMigration'],
      ],
    ];
  }

}

```


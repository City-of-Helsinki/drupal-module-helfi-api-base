# Migration

## Migration improvements

`\Drupal\helfi_api_base\Commands\MigrateHookCommands` adds two optional arguments to `migrate:import` command:

- `reset-threshold`: Resets migration status back to `Idle` if migration has been running longer than `reset-threshold`. For example, `--reset-threshold 43200` will reset migration back to `Idle` if the migration has been running for longer than 12 hours
- `interval`:  Limit how often migration can be run. For example, running the migration import with `--interval 3600` will only run it once an hour, regardless how often it's called

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


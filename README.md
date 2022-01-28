# Drupal api base

![CI](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/workflows/CI/badge.svg)

Base module for API based features.

## Requirements

- PHP 8.0 or higher

## Usage

### PO Importer

Create `translations/{langcode}` folders inside your module (like `translations/fi`, `translations/sv`) and create one or more `.po` files.

See https://www.drupal.org/community/contributor-guide/reference-information/localize-drupal-org/working-with-offline/po-and

Run `drush helfi:locale-import {module_name}`.

### Remote entity

@todo

### Migrate garbage collection

@todo

### Alter migration configuration

Create an event subscriber that responds to `\Drupal\helfi_api_base\Event\MigrationConfigurationEvent`  event:

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

## Contact

Slack: #helfi-drupal (http://helsinkicity.slack.com/)

Mail: helfi-drupal-aaaactuootjhcono73gc34rj2u@druid.slack.com

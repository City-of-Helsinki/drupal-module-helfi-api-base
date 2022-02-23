# Drupal api base

![CI](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/workflows/CI/badge.svg)

Base module for API based features.

## Requirements

- PHP 8.0 or higher

## PO Importer

Create `translations/{langcode}` folders inside your module (like `translations/fi`, `translations/sv`) and create one or more `.po` files.

See https://www.drupal.org/community/contributor-guide/reference-information/localize-drupal-org/working-with-offline/po-and

Run `drush helfi:locale-import {module_name}`.

## Remote entity

@todo:

## Migrate garbage collection

@todo

## Link filter

You can enable the filter from `Configuration -> Text formats and editors -> Configure -> Enable the Hel.fi: Link converter filter`. This must be run after `Convert URLs into links` filter if enabled.

The filter parses all links from markup fields and runs them through the `#type => link` render element so they can be processed the same way all other links are processed. See [src/Plugin/Filter/LinkConverter.php](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/blob/main/src/Plugin/Filter/LinkConverter.php).

## Link preprocessor

We override the default link `#preprocess` callback to run all our links through a template to figure out whether the link is external or not. See:
- [src/Link/LinkProcessor.php](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/blob/main/src/Link/LinkProcessor.php)
- [src/Helper/ExternalUri.php](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/blob/main/src/Helper/ExternalUri.php)
- [tests/themes/link_template_test_theme/templates/helfi-link.html.twig](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/blob/main/tests/themes/link_template_test_theme/templates/helfi-link.html.twig) (this is done in `hdbt` theme as well).

 ## Alter migration configuration

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

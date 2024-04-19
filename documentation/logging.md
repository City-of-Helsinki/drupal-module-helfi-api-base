# Logging

Errors caught by Drupal will be logged to `php://stdout` by `drupal/monolog` module.

JSON parsing needs to be enabled manually in order for this to work. See https://helsinkisolutionoffice.atlassian.net/wiki/spaces/HELFI/pages/7854817294/Logging+Structured+logs#Configuration-using-openshift-console.

Make sure your `settings.php` includes:

```php
if (file_exists('modules/contrib/helfi_api_base/monolog.services.yml')) {
  $GLOBALS['conf']['container_service_providers'][] = '\Drush\Drupal\DrushLoggerServiceProvider';
  $GLOBALS['conf']['container_service_providers'][] = '\Drupal\monolog\MonologServiceProvider';
  $settings['container_yamls'][] = 'modules/contrib/monolog/monolog.services.yml';
  $settings['container_yamls'][] = 'modules/contrib/helfi_api_base/monolog.services.yml';
}
```

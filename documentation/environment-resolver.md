# Environment resolver

`helfi_api_base.environment_resolver` service provides a way to fetch environment details for given project/environment.
`helfi_api_base.active_environment` service provides a way to fetch currently active environment.

## Usage

### Environment resolver
```php
// See \Drupal\helfi_api_base\Environment\Project for all available project constants.
$projectName = \Drupal\helfi_api_base\Environment\Project::ASUMINEN;
/** @var \Drupal\helfi_api_base\Environment\EnvironmentResolver $resolver */
$resolver = \Drupal::service('helfi_api_base.environment_resolver');
/** @var \Drupal\helfi_api_base\Environment\Environment $environment */
$environment = $resolver->getEnvironment($projectName, 'dev');
// A canonical URL.
$url = $environment->getUrl('fi');
// An internal address that is guaranteed to work for inter container communication (API requests for example).
$internalUrl = $environment->getInternalAddress('fi');
$path = $environment->getPath('fi');
$domain = $environment->getDomain();
$baseUrl = $environment->getBaseUrl();

// These will resolve to something like:
// $url = 'https://nginx-asuminen-dev.agw.arodevtest.hel.fi/fi/dev-asuminen';
// $path = '/fi/dev-asuminen';
// $domain = 'nginx-asuminen-dev.agw.arodevtest.hel.fi';
// $baseUrl = 'https://nginx-asuminen-dev.agw.arodevtest.hel.fi';
```

### Active environment

This requires `helfi_api_base.environment_resolver.settings` configuration to be set properly:

```php
# settings.php
$config['helfi_api_base.environment_resolver.settings']['environment_name'] = getenv('APP_ENV');
$config['helfi_api_base.environment_resolver.settings']['project_name'] = 'liikenne';
```

```php
/** @var \Drupal\helfi_api_base\Environment\EnvironmentResolver $resolver */
$resolver = \Drupal::service('helfi_api_base.environment_resolver');
/** @var \Drupal\helfi_api_base\Environment\Environment $environment */
// Fetches the currently active project and environment. For example liikenne dev.
$environment = $service->getActiveEnvironment();
// Fetches the currently active project. For example liikenne.
$project = $service->getActiveProject();
```

## Usage in other projects

The [environments.json](/fixtures/environments.json) file should contain up-to-date information of all our available projects in JSON format.

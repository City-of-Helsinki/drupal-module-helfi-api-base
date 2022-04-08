# Environment resolver

`helfi_api_base.environment_resolver` service provides a way to fetch environment details for given project/environment.

## Usage

```php
// See \Drupal\helfi_api_base\Environment\Project for all available project constants.
$projectName = \Drupal\helfi_api_base\Environment\Project::ASUMINEN;
/** @var \Drupal\helfi_api_base\Environment\EnvironmentResolver $resolver */
$resolver = \Drupal::service('helfi_api_base.environment_resolver');
/** @var \Drupal\helfi_api_base\Environment\Environment $environment */
$environment = $resolver->getEnvironment($projectName, 'dev');
$url = $environment->getUrl('fi');
$path = $environment->getPath('fi');
$domain = $environment->getDomain();
$baseUrl = $environment->getBaseUrl();

// These will resolve to something like:
// $url = 'https://nginx-asuminen-dev.agw.arodevtest.hel.fi/fi/dev-asuminen';
// $path = '/fi/dev-asuminen';
// $domain = 'nginx-asuminen-dev.agw.arodevtest.hel.fi';
// $baseUrl = 'https://nginx-asuminen-dev.agw.arodevtest.hel.fi';
```

You can use `internal` environment to reference to current instance. This is useful when you need to create API requests against current instance for example.

Internal environment will always default to `http://127.0.0.1:8080` domain.

## Usage in other projects

The [environments.json](/fixtures/environments.json) file should contain up-to-date information of all our available projects in JSON format.

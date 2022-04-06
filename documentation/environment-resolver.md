# Environment resolver

`helfi_api_base.environment_resolver` service provides a way to fetch environment details for given project/environment, like:
- Domain
- Paths per language

## Usage

```php
// See \Drupal\helfi_api_base\Environment\Project for all available project constants.
$projectName = \Drupal\helfi_api_base\Environment\Project::ASUMINEN;
/** @var \Drupal\helfi_api_base\Environment\EnvironmentResolver $resolver */
$resolver = \Drupal::service('helfi_api_base.environment_resolver');
$url = $resolver->getUrl($projectName, 'fi', 'dev');
$path = $resolver->getPath($projectName, 'fi', 'dev');
$domain = $resolver->getDomain($projectName, 'dev');

// These will resolve to something like:
// $url = 'https://nginx-asuminen-dev.agw.arodevtest.hel.fi/fi/dev-asuminen';
// $path = '/fi/dev-asuminen';
// $domain = 'nginx-asuminen-dev.agw.arodevtest.hel.fi';
```

## Usage in other projects

The [environments.json](/fixtures/environments.json) file should contain up-to-date information of all our available projects in JSON format.

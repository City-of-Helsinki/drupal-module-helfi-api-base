# Environment resolver

You can use `helfi_api_base.environment_resolver` service to fetch environment details, like full URL, paths or domain for given environment.

For example:

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

The [fixtures/environments.json](/fixtures/environments.json) file should contain up-to-date information of all our available projects in JSON format.

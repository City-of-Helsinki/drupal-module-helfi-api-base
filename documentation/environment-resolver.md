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
/** @var \Drupal\helfi_api_base\Environment\Project $project */
$project = $resolver->getProject($projectName);
/** @var \Drupal\helfi_api_base\Environment\ProjectMetadata $projectMetadata */
$projectMetadata = $project->getMetadata();
// A link to the Git repository.
$projectMetadata->getRepositoryUrl(); // 'https://github.com/City-of-Helsinki/drupal-helfi-asuminen'.

/** @var \Drupal\helfi_api_base\Environment\Environment $environment */
$environment = $resolver->getEnvironment($projectName, 'dev');

$url = $environment->getUrl('fi'); // 'https://nginx-asuminen-dev.agw.arodevtest.hel.fi/fi/dev-asuminen'.
// Internal address that is guaranteed to work for inter container communication (API requests for example).
$internalUrl = $environment->getInternalAddress('fi'); // 'https://helfi-asuminen.docker.so:8080/fi/asuminen'.
$path = $environment->getPath('fi'); // '/fi/dev-asuminen'.
$domain = $environment->getDomain(); // 'nginx-asuminen-dev.agw.arodevtest.hel.fi'.
$baseUrl = $environment->getBaseUrl(); // 'https://nginx-asuminen-dev.agw.arodevtest.hel.fi'
/** @var \Drupal\helfi_api_base\Environment\Service $services */
$service = $environment->getService(\Drupal\helfi_api_base\Environment\ServiceEnum::ElasticProxy); // Gets the elastic-proxy service.
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
// Failure will throw an InvalidArgumentException.
$project = $service->getActiveProject();
```

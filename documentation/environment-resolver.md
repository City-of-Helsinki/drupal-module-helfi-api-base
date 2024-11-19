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

### Active project roles

This can be used to determine what "roles" the currently active project has.

See [\Drupal\helfi_api_base\Environment\ProjectRoleEnum](/src/Environment/ProjectRolesEnum.php) for available roles.

```php
/** @var \Drupal\helfi_api_base\Environment\ActiveProjectRoles $activeProjectRoles */
$activeProjectRoles = \Drupal::service(\Drupal\helfi_api_base\Environment\ActiveProjectRoles::class);
// A boolean indicating whether the current instance has the given role.
$activeProjectRoles->hasRole(\Drupal\helfi_api_base\Environment\ProjectRoleEnum::Core);
```

#### Determine if the current instance is a "core" instance

The instance is considered as "core" instance if the instance is a part of the main-navigation structure. Currently, this includes all Drupal instances under www.hel.fi domain.

This can be used to conditionally run code only in core instances, such as enabling a module.

```php
/** @var \Drupal\helfi_api_base\Environment\ActiveProjectRoles $activeProjectRoles */
$activeProjectRoles = \Drupal::service(\Drupal\helfi_api_base\Environment\ActiveProjectRoles::class);

if ($activeProjectRoles->hasRole(\Drupal\helfi_api_base\Environment\ProjectRoleEnum::Core)) {
  // Do something only in core instances.
}
```


# Feature toggle

Provides a service to conditionally check if the given feature is enabled.

## Usage

```php
$service = \Drupal::service(\Drupal\helfi_api_base\Features\FeatureManager::class);

$service->isEnabled(\Drupal\helfi_api_base\Features\FeatureManager::LOGGER); // Returns true if the logger feature is enabled.
// Disables the logger feature.
$service->disableFeature(\Drupal\helfi_api_base\Features\FeatureManager::LOGGER)
// Disables the logger feature.
$service->enableFeature(\Drupal\helfi_api_base\Features\FeatureManager::LOGGER);
```

## Development

Add a new configuration under `helfi_api_base.features` schema in `config/schema/helfi_api_base.schema.yml` file.

```yaml
helfi_api_base.features:
  ...
  yournewfeature:
    type: boolean
```

Add a constant to `src/Features/FeatureManagerInterface.php`:
```php
// The value should be the same as in schema.yml file.
public const YOURNEWFEATURE = 'yournewfeature';
```

Add the default value to `config/install/helfi_api_base.features.yml`.

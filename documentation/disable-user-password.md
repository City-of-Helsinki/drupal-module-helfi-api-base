# Disable user password

Provides a [Deployment hook](/documentation/deploy-hooks.md) that sets listed users' password to NULL.

## Usage

Run `drush helfi:post-deploy` to trigger this.

## Configuration

See `helfi_api_base.disable_password_users` parameter in [helfi_api_base.services.yml](/helfi_api_base.services.yml) for default value.

Users are loaded by `uid`, `mail` and `name` field.

The value can be overridden in one of these files:

```yaml
# public/sites/default/services.yml
# public/sites/default/{env}.services.yml
parameters:
  helfi_api_base.disable_password_users:
    - test@example.com
    - 1
    - 'some-user-name'
```

or dynamically in service provider class: https://www.drupal.org/docs/drupal-apis/services-and-dependency-injection/altering-existing-services-providing-dynamic-services.

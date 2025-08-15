# User login restrictions

This feature is used to restrict login attempts to private networks for configure roles. It is implemented with an [event listener](../src/EventSubscriber/UserLoginSubscriber.php).

## Configuration

See `helfi_api_base.restricted_roles` parameter in [helfi_api_base.services.yml](/helfi_api_base.services.yml).

The value can be overridden in one of these files:

```yaml
# public/sites/default/services.yml
# public/sites/default/{env}.services.yml
parameters:
  helfi_api_base.restricted_roles:
    - 'some-role-name'
```

or dynamically in service provider class: https://www.drupal.org/docs/drupal-apis/services-and-dependency-injection/altering-existing-services-providing-dynamic-services.

# API account mapper

Allows API accounts to be created from defined environment variables. The accounts are processed by `drush helfi:post-deploy` command during the deployment.

## Mapping accounts

Define an environment variable starting with `DRUPAL_API_ACCOUNT_`. For example `DRUPAL_API_ACCOUNT_MENU`. These are mapped in [settings.php](https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/public/sites/default/settings.php) file shipped with `City-of-Helsinki/drupal-helfi-platform`.

The value should be a JSON string that contains required `username`, `password` and an optional `roles`:

```json
{"username": "menu-api", "password": "your-password", "roles": ["role1", "role2"]}
```

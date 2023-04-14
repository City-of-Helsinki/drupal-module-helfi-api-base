# API account mapper

Allows API accounts to be created from an environment variable. The accounts are processed by `drush helfi:post-deploy` command during the deployment.

## Mapping accounts

Define an environment variable called `DRUPAL_API_ACCOUNTS`. These accounts are read and mapped in [settings.php](https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/public/sites/default/settings.php) file shipped with `City-of-Helsinki/drupal-helfi-platform`.

The value should be a JSON string that contains an array of `username`, `password` and an optional `roles` pairs:

```bash
DRUPAL_API_ACCOUNTS='[{"username":"account1","password":"password1","roles":["role1","role2"]},{"username":"account2","password":"password2"}]'
```

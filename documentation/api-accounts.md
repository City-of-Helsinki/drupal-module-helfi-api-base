# API user manager

Allows API user credentials to be specified in an environment variable.

This can be used to ensure that API users always retain the same credentials, i.e. it creates any missing accounts and then force resets the password.

## Mapping accounts

Define an environment variable called `DRUPAL_API_ACCOUNTS`. These accounts are read and mapped in [settings.php](https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/public/sites/default/settings.php) file shipped with `City-of-Helsinki/drupal-helfi-platform`.

The value should be a base64 encoded JSON string that contains an array of `username`, `password` and an optional `roles` and `mail` pairs:

```bash
php -r "print base64_encode('[{"username":"account1","password":"password1","roles":["role1","role2"]},{"username":"account2","password":"password2","mail":"some-email@example.com"}]');"
```
Then map the given output to `DRUPAL_API_ACCOUNTS` environment variable:

```bash
DRUPAL_API_ACCOUNTS=W3t1c2VybmFtZTphY2NvdW50MSxwYXNzd29yZDpwYXNzd29yZDEscm9sZXM6W3JvbGUxLHJvbGUyXX0se3VzZXJuYW1lOmFjY291bnQyLHBhc3N3b3JkOnBhc3N3b3JkMixtYWlsOnNvbWUtZW1haWxAZXhhbXBsZS5jb219XQ==
```

If no `mail` is provided, an email address like `drupal+$username@hel.fi` is used. For example: `drupal+account1@hel.fi`.

We hook into `helfi_api_base.post_deploy` event ([src/EventSubscriber/EnsureApiAccountsSubscriber.php](/src/EventSubscriber/EnsureApiAccountsSubscriber.php)), triggered by `drush helfi:post-deploy` command executed as a part of deployment tasks: [https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/docker/openshift/entrypoints/20-deploy.sh](https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/docker/openshift/entrypoints/20-deploy.sh)

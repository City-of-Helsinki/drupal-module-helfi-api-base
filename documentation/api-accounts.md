# API credential manager

Allows API user credentials to be specified in an environment variables.

This can be used to:
 - [Ensure that API users always retain the same credentials, i.e. it creates any missing accounts and then force resets the password](#managing-local-api-accounts)
 - [Store external API credentials](#managing-external-api-credentials)

## Managing local API accounts

This is used to ensure that local API accounts retain the credentials. Any missing accounts are created, and the password is reset to whatever is defined in the configuration.

### Configuration

Define an array of `username`, `password` and an optional `roles` and `mail` pairs:

```php
$config['helfi_api_base.api_accounts']['accounts'][] = [
  'username' => 'account1',
  'password' => 'password1',
  'roles' => ['role1', 'role2'],
  'mail' => 'some-email@example.com',
];
```

If no `mail` is provided, an autogenerated email address like `drupal+$username@hel.fi` is used. For example: `drupal+account1@hel.fi`.

### Using environment variable to define accounts

Define an environment variable called `DRUPAL_API_ACCOUNTS`. These accounts are read and mapped in [settings.php](https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/public/sites/default/settings.php) file shipped with `City-of-Helsinki/drupal-helfi-platform`.

The value should be a base64 encoded JSON string of whatever is defined in `helfi_api_base.api_accounts.accounts` configuration, for example:

```bash
php -r "print base64_encode('[{"username":"account1","password":"password1","roles":["role1","role2"]},{"username":"account2","password":"password2","mail":"some-email@example.com"}]');"
```
Then map the given output to `DRUPAL_API_ACCOUNTS` environment variable:

```bash
DRUPAL_API_ACCOUNTS=W3t1c2VybmFtZTphY2NvdW50MSxwYXNzd29yZDpwYXNzd29yZDEscm9sZXM6W3JvbGUxLHJvbGUyXX0se3VzZXJuYW1lOmFjY291bnQyLHBhc3N3b3JkOnBhc3N3b3JkMixtYWlsOnNvbWUtZW1haWxAZXhhbXBsZS5jb219XQ==
```

### Usage

We hook into `helfi_api_base.post_deploy` event ([src/EventSubscriber/EnsureApiAccountsSubscriber.php](/src/EventSubscriber/EnsureApiAccountsSubscriber.php)), triggered by `drush helfi:post-deploy` command executed as a part of deployment tasks: [https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/docker/openshift/entrypoints/20-deploy.sh](https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/docker/openshift/entrypoints/20-deploy.sh)

### Testing locally

Add something like this to your `local.settings.php`:

```php
# local.settings.php
$api_accounts = [
  [
    'username' => 'helfi-debug-data',
    'password' => '123',
    'mail' => 'drupal+debug_api@hel.fi',
    'roles' => ['debug_api'],
  ],
];
$config['helfi_api_base.api_accounts']['accounts'] = $api_accounts;
```

## Managing external API credentials

This is used to store external API credentials.

### Configuration

Define an array of `id`, `plugin`, and `data` pairs:

```php
$config['helfi_api_base.api_accounts']['vault'][] = [
  'id' => 'pubsub',
  'plugin' => 'json',
  'data' => '{"endpoint": "xxx.docker.so", "hub": "local", "group": "invalidate_cache", "access_key": "<access-key>"}',
];
$config['helfi_api_base.api_accounts']['vault'][] = [
  'id' => 'global_navigation',
  'plugin' => 'authorization_token',
  'data' => 'aGVsZmktYWRtaW46MTIz',
];
```

The value of `data` field depends on `plugin` value:

- Authorization token (`authorization_token`): A simple string. For example `aGVsZmktYWRtaW46MTIz`.
- JSON (`json`): A JSON string. For example `{"endpoint": "xxxx.docker.so", "key": "value"}`.

### Using environment variable to define Vault items

Define an environment variable called `DRUPAL_VAULT_ACCOUNTS`. These accounts are read and mapped in [settings.php](https://github.com/City-of-Helsinki/drupal-helfi-platform/blob/main/public/sites/default/settings.php) file shipped with `City-of-Helsinki/drupal-helfi-platform`.

The value should be a base64 encoded JSON string of whatever is defined in `helfi_api_base.api_accounts.vault` configuration, for example:

```bash
php -r "print base64_encode('[{"id": "global_navigation", "plugin": "authorization_token": "data": "aGVsZmktYWRtaW46MTIz"}]');"
```

Then map the given output to `DRUPAL_VAULT_ACCOUNTS` environment variable:

```bash
DRUPAL_VAULT_ACCOUNTS=W3tpZDogZXR1c2l2dV9sb2NhbCwgcGx1Z2luOiBhdXRob3JpemF0aW9uX3Rva2VuOiBkYXRhOiBhR1ZzWm1rdFlXUnRhVzQ2TVRJen1d
```

### Usage

```php
/** @var \Drupal\helfi_api_base\Vault\VaultManager $service */
$service = \Drupal::service('helfi_api_base.vault_manager');
/** @var \Drupal\helfi_api_base\Vault\VaultItemInterface $item */
$item = $service->get('global_navigation'); // 'global_navigation' is the ID previously defined in DRUPAL_VAULT_ACCOUNTS.
$id = $item->id(); // $id = 'global_navigation'.
$data = $item->data() // $data = 'aGVsZmktYWRtaW46MTIz'. This is a base64 encoded basic auth token (helfi-admin:123).
```

### Testing locally

Add something like this to your `local.settings.php`:

```php
# local.settings.php
$vault_accounts = [
  [
    'id' => 'etusivu_local',
    'plugin' => 'authorization_token',
    'data' => base64_encode('helfi-debug-data:123'),
  ],
];
$config['helfi_api_base.api_accounts']['vault'] = $vault_accounts;
```

## Tool to create/update the secret

See https://helsinkisolutionoffice.atlassian.net/wiki/spaces/HEL/pages/6785826654/Ymp+rist+t (in Finnish) for more information on how to actually update the value for given environment variable.

This module provides a Drush command to easily update and create API secrets. The command returns a base64 encoded string that can directly be copied to Azure Key Vault.

### Update

Use `drush helfi:update-api-secret` command to update the API secret.

### Create

Use `drush helfi:create-api-secret` command to create the API secret.

### Show current value

Call `drush helfi:reveal-api-secret {secret value}` to show the value of given secret.

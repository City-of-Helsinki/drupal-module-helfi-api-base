# Testing

## Mock final classes

Install https://github.com/dg/bypass-finals package: `composer require dg/bypass-finals --dev`.

Add this phpunit hook to your `phpunit.xml` file:

```xml
<extensions>
  <extension class="\Drupal\helfi_api_base\BypassFinalHook"/>
</extensions>
```

## Fixture commands

Provides a way of defining migration fixtures in code that can either be used in tests or imported using Drush.

See [drupal-module-helfi-tpr/src/Fixture](https://github.com/City-of-Helsinki/drupal-module-helfi-tpr/tree/main/src/Fixture) for a complete example.

### Creating a migration fixture

Create a class that extends `Drupal\helfi_api_base\Fixture\FixtureBase` and define required `getMockResponses() : array` method.

The method above should return an array of `\GuzzleHttp\Psr7\Response[]` objects that should return in identical format with your API endpoint and should meet the requirements of your migration's source plugin.

Define a service for your fixture:
```php
# yourmodule.services.yml
services:
  migration_fixture.{migrate_id}:
    class: Drupal\helfi_tpr\Fixture\Service
```

### Running a fixture migration

Call `drush helfi:migrate-fixture {migrate_id}` to run a fixture migration.

## Testing migrations

This module provides a way to test migrations in `Functional` and `Kernel` tests.

You have to replace core's `http_client` service with GuzzleHttp's MockHandler client to return fixture data for your migration. For example:

```php
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use Drupal\Tests\helfi_api_base\Traits\MigrationTestTrait;
# ...
// Replaces the http_client service with mock client that will yield responses
// from the migration fixture service defined earlier.
// This can be replaced with an array of \GuzzleHttp\Psr7\Response objects.
$responses = $this->container->get('migration_fixture.tpr_service')->getMockResponses();
// ::createMockHttpClient() method is provided by ApiTestTrait.
$this->container->set('http_client', $this->createMockHttpClient($responses));
// Run migration using fake data. ::executeMigration() method is defined in
// MigrationTestTrait.
$this->executeMigration('tpr_service');
```

See [drupal-module-helfi-tpr/tests/src/Kernel/UnitMigrationTest.php](https://github.com/City-of-Helsinki/drupal-module-helfi-tpr/blob/main/tests/src/Kernel/UnitMigrationTest.php) for an example.

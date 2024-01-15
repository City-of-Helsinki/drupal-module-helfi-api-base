# API client

Service for HTTP JSON APIs.

Features:
 - Simple caching.
 - Optional response mocking for local environment.

## Usage

Create your own client service from abstract service `helfi_api_base.api_client_base`. You must provide your own logger. Optionally you can provide default request parameters.

```yaml
# my_module.services.yml
my_module.my_api:
  parent: helfi_api_base.api_manager
  arguments:
    - '@logger.channel.my_module'
    # Optional:
    - { timeout: 30 }
```

Actual requests are usually made in the callback of `cache()` method. The callback must return `CacheValue`.

```php
use Drupal\helfi_api_base\ApiClient\CacheValue;

/** @var Drupal\helfi_api_base\ApiClient\ApiClient $client */
$client = \Drupal::service('my_module.my_api');

$response = $client->cache($id, fn () => new CacheValue(
  // Actual HTTP response.
  $client->makeRequest('GET', 'https://example.com/api/v1/foo'),
  // TTL.
  $client->cacheMaxAge(ttl: 180),
  // Custom cache tags.
  ['user:1']
));
```

### Mocking

In local environment, the `makeRequestWithFixture` method returns response from JSON file if the response fails.

```php
$client->makeRequestWithFixture('path-to-fixture.json', 'GET', 'https://example.com/fails-in-local'),
```

*Warning*: The client fail any further requests to `makeRequestWithFixture` instantly after one failed requests. This is to prevent blocking the rendering process and cause the site to time-out. You should not share the client for different purposes that need fault tolerance.

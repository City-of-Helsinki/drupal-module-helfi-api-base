# API client

Base service for HTTP JSON APIs.

Features:
 - Simple caching.
 - Optional response mocking for local environment.
 - Fail any further request instantly after one failed request.

## Usage

Extend `\Drupal\helfi_api_base\ApiClient\ApiClient`. Requests are made in the callbacks of `cache()` method.

```php
namespace Drupal\my_module;

use Drupal\helfi_api_base\ApiClient\ApiClient;
use Drupal\helfi_api_base\ApiClient\CacheValue;

class MyApi extends ApiClient {

  const TTL = 180;

  public function getFoo(string $id) {
    return $this->cache($id, fn () => new CacheValue(
      $this->makeRequest('GET', 'https://example.com/api/v1/foo'),
      $this->time->getRequestTime() + self::TTL,
      ['user:1']
    ))->response;
  }

}
```

### Service

Extend your service from the abstract service `helfi_api_base.api_client_base`. You must provide your own logger. Optionally you can provide default request parameters and headers.

```yaml
# my_module.services.yml
my_module.my_api:
  parent: helfi_api_base.api_manager
  class: Drupal\my_module\MyApi
  arguments:
    - '@logger.channel.my_module'
    - { timeout: 30 }
```

### Mocking

In local environment, the `makeRequestWithFixture` method returns response from JSON file if the response fails.

```php
class MyApi extends ApiClientBase {

  public function getFoo(string $id) {
    return $this->cache($id, fn () => new CacheValue(
      $this->makeRequestWithFixture('path-to-fixture.json', 'GET', 'https://example.com/fail'),
      $this->time->getRequestTime() + self::TTL,
      ['user:1']
    ))->response;
  }

}
```

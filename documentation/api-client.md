# API client

Base service for calling HTTP JSON APIs.

Features:
 - Simple request caching.
 - Optional response mocking on local environments.

## Usage

Extend `\Drupal\helfi_api_base\ApiClient\ApiClient`.

```php
namespace Drupal\my_module;

use Drupal\helfi_api_base\ApiClient\ApiClientBase;
use Drupal\helfi_api_base\ApiClient\CacheValue;

class MyApi extends ApiClientBase {

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

### Service configuration

Extend your service from the abstract service `helfi_api_base.api_client_base`. By default, you must provide your own logger and optional authorization service and default request parameters.

```yaml
# my_module.services.yml
my_module.my_api:
  parent: helfi_api_base.api_manager
  class: Drupal\my_module\MyApi
  arguments:
    - '@logger.channel.my_module'
    - '@my_module.authorization'
    - { timeout: 30 }
```

### Adding authorization to requests

Authorization headers can be added create and authorization service. For simple use case, extend the vault authorizer. The authorizer requires name of the vault you are going to use. Pass this service to your client like in the previous example.

```yaml
# my_module.services.yml
my_module.authorization:
  parent: helfi_api_base.vault_authorizer
  arguments:
    - 'my_module.vault_key'
```

### Mocking requests

If a requests fails, the client can serve mock responses in the local environment. Use `fixture` parameter of the `makeRequest` function.

```php
class MyApi extends ApiClientBase {

public function getFoo(string $id) {
  return $this->cache($id, fn () => new CacheValue(
    $this->makeRequest('GET', 'https://example.com/fail', fixture: '/tmp/fixture.json'),
    $this->time->getRequestTime() + self::TTL,
    ['user:1']
  ))->response;
}

}
```

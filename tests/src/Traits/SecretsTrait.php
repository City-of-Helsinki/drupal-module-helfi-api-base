<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Traits;

/**
 * A trait to deal with secrets.
 */
trait SecretsTrait {

  /**
   * Gets the secret by key.
   *
   * @param string $key
   *   The secret by key.
   *
   * @return string|null
   *   The secret value or null.
   */
  public function getSecret(string $key): ?string {
    static $data = [];

    if (!$data) {
      $file = DRUPAL_ROOT . '/../.secrets.json';

      if (!file_exists($file)) {
        throw new \RuntimeException('Failed to open .secrets.json file');
      }
      $data = json_decode(file_get_contents($file), TRUE, flags: JSON_THROW_ON_ERROR);
    }
    return $data[$key] ?? NULL;
  }

}

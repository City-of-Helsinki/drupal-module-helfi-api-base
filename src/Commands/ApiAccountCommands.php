<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Commands;

use Drush\Attributes\Command;
use Drush\Commands\DrushCommands;

/**
 * A drush command file to manage API accounts.
 */
final class ApiAccountCommands extends DrushCommands {

  /**
   * Updates the base64 and json encoded Azure secret.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:update-api-secret')]
  public function update() : int {
    $type = $this->askType();

    if (!$value = $this->io()->ask('The base64 and JSON encoded secret value')) {
      throw new \InvalidArgumentException('No value given.');
    }
    $values = json_decode(base64_decode($value), TRUE, flags: JSON_THROW_ON_ERROR);

    $this->io()
      ->note(sprintf('Current value: %s', json_encode($values)));

    $values = array_merge($values, [$this->processValues($type)]);

    $this->io()->note(sprintf('New value: %s', json_encode($values)));
    $this->io()
      ->writeln(
        vsprintf('Copy paste this value to Azure Key vault [%s]: %s', [
          $type,
          base64_encode(json_encode($values)),
        ])
      );

    return DrushCommands::EXIT_SUCCESS;
  }

  /**
   * Creates a base64 and json encoded Azure secret.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:create-api-secret')]
  public function create() : int {
    $type = $this->askType();

    $values = [$this->processValues($type)];

    $this->io()->note(sprintf('The value: %s', json_encode($values)));
    $this->io()
      ->writeln(
        vsprintf('Copy paste this value to Azure Key vault [%s]: %s', [
          $type,
          base64_encode(json_encode($values)),
        ])
      );
    return DrushCommands::EXIT_SUCCESS;
  }

  /**
   * Prompts the user for the secret type.
   *
   * @return string
   *   The secret type.
   */
  private function askType() : string {
    $type = $this->io()
      ->ask('The type of given secret [1 = DRUPAL_VAULT_ACCOUNTS or 2 = DRUPAL_API_ACCOUNTS]', '2');

    return match($type) {
      '1' => 'DRUPAL_VAULT_ACCOUNTS',
      '2' => 'DRUPAL_API_ACCOUNTS',
      default => throw new \InvalidArgumentException('Invalid secret type'),
    };
  }

  /**
   * Processes the field values.
   *
   * @param string $type
   *   The secret type.
   *
   * @return array
   *   The processed field values.
   */
  private function processValues(string $type) : array {
    $values = [];

    foreach ($this->getFields($type) as $name => $options) {
      [
        'description' => $description,
        'default_value' => $defaultValue,
        'callback' => $callback,
      ] = $options;

      if (!$callback) {
        $callback = function ($value) {
          return trim($value);
        };
      }

      $value = $this->io()
        ->ask(sprintf('Provide %s [%s]', $name, $description), $defaultValue);

      if (!$value) {
        continue;
      }
      $values[$name] = $callback($value);
    }
    return $values;
  }

  /**
   * Gets the fields for given type.
   *
   * @param string $type
   *   The type.
   *
   * @return array[]
   *   The fields.
   */
  private function getFields(string $type) : array {
    return match($type) {
      'DRUPAL_VAULT_ACCOUNTS' => [
        'id' => [
          'description' => 'An unique ID for given item',
          'default_value' => NULL,
          'callback' => NULL,
        ],
        'plugin' => [
          'description' => 'The plugin',
          'default_value' => 'authorization_token',
          'callback' => NULL,
        ],
        'data' => [
          'description' => 'The data. An authorization token or basic auth string for example.',
          'default_value' => NULL,
          'callback' => NULL,
        ],
      ],
      'DRUPAL_API_ACCOUNTS' => [
        'username' => [
          'description' => 'The username',
          'default_value' => NULL,
          'callback' => NULL,
        ],
        'password' => [
          'description' => 'The password',
          'default_value' => NULL,
          'callback' => NULL,
        ],
        'mail' => [
          'description' => 'Leave empty to use automatically generated email',
          'default_value' => NULL,
          'callback' => NULL,
        ],
        'roles' => [
          'description' => 'A comma separated list of roles',
          'default_value' => NULL,
          'callback' => function (string $value) : array {
            return array_map('trim', explode(',', $value));
          },
        ],
      ],
    };
  }

}

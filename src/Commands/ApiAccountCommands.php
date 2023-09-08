<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\Core\Password\PasswordGeneratorInterface;
use Drush\Attributes\Command;
use Drush\Attributes\Option;
use Drush\Commands\DrushCommands;

/**
 * A drush command file to manage API accounts.
 */
final class ApiAccountCommands extends DrushCommands {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Password\PasswordGeneratorInterface $passwordGenerator
   *   The password generator service.
   */
  public function __construct(
    private PasswordGeneratorInterface $passwordGenerator,
  ) {
  }

  /**
   * Reveals the secret value.
   *
   * @param string $value
   *   The value.
   *
   * @return int
   *   The exit code.
   */
  #[Command(name: 'helfi:reveal-api-secret')]
  public function reveal(string $value) : int {
    $currentValue = json_decode(base64_decode($value), flags: JSON_THROW_ON_ERROR);
    $this->io()->note('Current value:');
    $this->io()->writeln(json_encode($currentValue, flags: JSON_PRETTY_PRINT));

    return DrushCommands::EXIT_SUCCESS;
  }

  /**
   * Updates the base64 and json encoded Azure secret.
   *
   * @param array $options
   *   The options.
   *
   * @return int
   *   The exit code.
   *
   * @throws \JsonException
   */
  #[Command(name: 'helfi:update-api-secret')]
  #[Option(name: 'filename', description: 'An optional filename to read the secret from.')]
  #[Option(name: 'type', description: 'The type of given secret. [1 = DRUPAL_VAULT_ACCOUNTS or 2 = DRUPAL_API_ACCOUNTS')]
  #[Option(name: 'id', description: 'An unique ID for given item.')]
  #[Option(name: 'plugin', description: 'The plugin')]
  #[Option(name: 'data', description: 'The data.')]
  public function update(array $options = [
    'value' => NULL,
    'type' => NULL,
    'id' => NULL,
    'plugin' => NULL,
    'data' => NULL,
  ]) : int {
    $type = $this->askType($options['type']);

    if ($options['filename']) {
      $value = file_get_contents($options['filename']);
    }
    else {
      $value = $this->io()->ask('The base64 and JSON encoded secret value');

      if ($value && strlen($value) >= 4095) {
        throw new \InvalidArgumentException('The secret value is longer than 4096 bytes and will be truncated by your terminal. Use --file to pass a file to read the content from instead.');
      }
    }
    if (!$value) {
      throw new \InvalidArgumentException('No value given.');
    }

    $values = json_decode(base64_decode(trim($value)), TRUE, flags: JSON_THROW_ON_ERROR);

    $this->io()
      ->note(sprintf('Current value: %s', json_encode($values, flags: JSON_PRETTY_PRINT)));

    $values = array_merge($values, [$this->processValues($type, $options)]);

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
   * @param string|null $type
   *   The type.
   *
   * @return string
   *   The secret type.
   */
  private function askType(string $type = NULL) : string {
    if (!$type) {
      $type = $this->io()
        ->ask('The type of given secret [1 = DRUPAL_VAULT_ACCOUNTS or 2 = DRUPAL_API_ACCOUNTS]', '2');
    }

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
   * @param array $options
   *   The default options.
   *
   * @return array
   *   The processed field values.
   */
  private function processValues(string $type, array $options = []) : array {
    $values = [];

    foreach ($this->getFields($type) as $name => $option) {
      [
        'description' => $description,
        'default_value' => $defaultValue,
        'callback' => $callback,
      ] = $option;

      if (!$callback) {
        $callback = function ($value) {
          return trim($value);
        };
      }

      if ((!isset($options[$name])) || is_null($options[$name])) {
        $options[$name] = $this->io()
          ->ask(sprintf('Provide %s [%s]', $name, $description), $defaultValue);
      }
      $value = $callback($options[$name]);

      if (!$value) {
        continue;
      }
      $values[$name] = $value;
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
          'default_value' => '',
          'callback' => NULL,
        ],
        'plugin' => [
          'description' => 'The plugin',
          'default_value' => 'authorization_token',
          'callback' => NULL,
        ],
        'data' => [
          'description' => 'The data. An authorization token or basic auth string for example.',
          'default_value' => '',
          'callback' => NULL,
        ],
      ],
      'DRUPAL_API_ACCOUNTS' => [
        'username' => [
          'description' => 'The username',
          'default_value' => '',
          'callback' => NULL,
        ],
        'password' => [
          'description' => 'The password. Leave empty to generate a random password.',
          'default_value' => NULL,
          'callback' => function (?string $value) : string {
            return $value ?: $this->passwordGenerator->generate(30);
          },
        ],
        'mail' => [
          'description' => 'Leave empty to use automatically generated email',
          'default_value' => '',
          'callback' => NULL,
        ],
        'roles' => [
          'description' => 'A comma separated list of roles',
          'default_value' => '',
          'callback' => function (string $value) : array {
            return array_map('trim', explode(',', $value));
          },
        ],
      ],
    };
  }

}

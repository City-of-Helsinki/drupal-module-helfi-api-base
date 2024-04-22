<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * The migration configuration event.
 */
final class MigrationConfigurationEvent extends Event {

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   The configuration.
   * @param \Drupal\migrate\Plugin\MigrationInterface|null $migration
   *   The migration or null.
   */
  public function __construct(
    public array &$configuration,
    public ?MigrationInterface $migration = NULL,
  ) {
  }

}

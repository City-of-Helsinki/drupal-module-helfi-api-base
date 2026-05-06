<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ActionLog\DTO;

/**
 * Entity update action.
 */
final readonly class EntityUpdateAction {

  public function __construct(
    public int $id,
    public int $previousRevision,
    public string $entity_type,
    public string $langcode,
  ) {

  }

}

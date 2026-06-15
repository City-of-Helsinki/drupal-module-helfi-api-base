<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\AuditLog;

use Drupal\Core\Entity\EntityInterface;

/**
 * Describes an entity type whose operations are written to the audit log.
 *
 * Built from the 'helfi_api_base.audit_log_entity_types' service parameter.
 */
final readonly class AuditLogEntityType {

  /**
   * Constructs a new instance.
   *
   * @param string $entityType
   *   The entity type id to match.
   * @param string|null $bundle
   *   The bundle to match, or NULL to match any bundle.
   * @param array<\Drupal\helfi_api_base\AuditLog\AuditLogOperation> $operations
   *   The operations that should be logged for this entity type.
   */
  public function __construct(
    public string $entityType,
    public ?string $bundle,
    public array $operations,
  ) {
  }

  /**
   * Creates an instance from a service parameter matcher.
   *
   * Operations are configured as strings and parsed into the AuditLogOperation
   * enum. When no operations are configured the write operations are used.
   * READ must be opted into explicitly.
   *
   * @param array{entity_type: string, bundle?: string, operations?: array<string>} $matcher
   *   The configured matcher.
   *
   * @return self
   *   The entity type matcher.
   */
  public static function fromArray(array $matcher): self {
    $operations = isset($matcher['operations'])
      ? array_values(array_filter(array_map(
        static fn (string $operation) => AuditLogOperation::tryFrom($operation),
        $matcher['operations'],
      )))
      : AuditLogOperation::defaultEntityOperations();

    return new self(
      entityType: $matcher['entity_type'],
      bundle: $matcher['bundle'] ?? NULL,
      operations: $operations,
    );
  }

  /**
   * Checks whether an operation on an entity should be logged.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operation was performed on.
   * @param \Drupal\helfi_api_base\AuditLog\AuditLogOperation $operation
   *   The operation being performed.
   *
   * @return bool
   *   TRUE if the operation on the entity should be logged, FALSE otherwise.
   */
  public function isLoggable(EntityInterface $entity, AuditLogOperation $operation): bool {
    if ($this->entityType !== $entity->getEntityTypeId()) {
      return FALSE;
    }
    // A NULL bundle matches any bundle.
    if ($this->bundle !== NULL && $this->bundle !== $entity->bundle()) {
      return FALSE;
    }
    return in_array($operation, $this->operations, TRUE);
  }

}

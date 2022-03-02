# Remote entity

A base entity to be used with migrations.

Provides a support for:
- Revision UI and routes
- Delete protection
- Automatic deletion

## Usage

See [tests/modules/remote_entity_test/src/Entity/RemoteEntityTest.php](tests/modules/remote_entity_test/src/Entity/RemoteEntityTest.php) for an example implementation.

## Revision support

@todo

## Delete protection

Deletion of remote entities is disabled for entities without a `delete-form` link template. See `::delete()` in [src/Entity/RemoteEntityBase.php](/src/Entity/RemoteEntityBase.php)

## Automatic deletion

Entities will be protected against accidental deletion by requiring the source entity to be absent at least `>= RemoteEntityBase::MAX_SYNC_ATTEMPT` times (2 by default).

A `sync_attempts` field is added to all entities that holds the number of attempts to update the given entity. If `sync_attempts` value exceeds the `MAX_SYNC_ATTEMPTS` value of your entity class, the entity will be automatically deleted.

This feature can be disabled by setting 'max sync attempts' value to zero (`public const MAX_SYNC_ATTEMPTS = 0`) in your entity class.

# Automatic external cache invalidation

Provides a way to invalidate arbitrary cache tags across all instances.

See [PubSub messaging](/documentation/pubsub-messaging.md) for documentation about underlying architecture and required settings.

For example, this can be used to invalidate:
- [News list paragraph blocks](https://github.com/City-of-Helsinki/drupal-helfi-platform-config) when News content is modified on Etusivu instance
- [Global navigation menu blocks](https://github.com/City-of-Helsinki/drupal-module-helfi-navigation/) when navigation is modified
- [Global announcement blocks](https://github.com/City-of-Helsinki/drupal-helfi-platform-config) when Etusivu modifies announcement content

## Usage

Use `helfi_api_base.cache_tag_invalidator` to invalidate arbitrary cache tags in all instances.

```php
/** @var \Drupal\helfi_api_base\Cache\CacheTagInvalidator $service */
$service = \Drupal::service('helfi_api_base.cache_tag_invalidator');
$service->invalidateTags(['an array of cache tags']);
```

This can be run automatically every time a CRUD operation is performed on certain entity. For example:

```php
/**
 * Invalidate external caches.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity to flush cache tags for.
 */
function helfi_etusivu_invalidate_external_caches(EntityInterface $entity) : void {
  // Only flush caches when we're saving announcement node.
  $isAnnouncementNode = $entity->getEntityTypeId() === 'node' && $entity->bundle() === 'announcement';

  if (!$isAnnouncementNode) {
    return;
  }
  /** @var \Drupal\helfi_api_base\Cache\CacheTagInvalidator $service */
  $service = \Drupal::service('helfi_api_base.cache_tag_invalidator');
  $service->invalidateTags(['helfi_external_entity_announcement']);
}

/**
 * Implements hook_entity_update().
 */
function helfi_etusivu_entity_update(EntityInterface $entity) : void {
  helfi_etusivu_invalidate_external_caches($entity);
}

/**
 * Implements hook_entity_delete().
 */
function helfi_etusivu_entity_delete(EntityInterface $entity) : void {
  helfi_etusivu_invalidate_external_caches($entity);
}

/**
 * Implements hook_entity_insert().
 */
function helfi_etusivu_entity_insert(EntityInterface $entity) : void {
  helfi_etusivu_invalidate_external_caches($entity);
}
```

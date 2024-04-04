# Automatic entity revision deletion

Allows old revisions to be deleted. By default, five revisions are kept per entity translation.

## Configuration

Allowed entity types can be configured in code with:
```php

# public/sites/default/*.settings.php
$config['helfi_api_base.delete_revisions']['entity_types'] = [
  'node',
  'paragraph',
  'tpr_errand_service',
];
```

or by creating a configuration yml file:

```yaml
# conf/cmi/helfi_api_base.delete_revisions.yml
entity_types:
  - node
  - paragraph
  - tpr_errand_service
```

The number of revisions to keep can be configured in `settings.php` with:
```php
$config['helfi_api_base.delete_revisions']['keep'] = 10;
```
or in `yml` file:

```yaml
# conf/cmi/helfi_api_base.delete_revisions.yml
keep: 10
```

## Usage

All configured entities are queued for clean up on entity save.

To clean up existing entities at once, run: `drush helfi:revision:delete {entity_type}`.

# Automatic entity revision deletion

Allows old revisions to be deleted. By default, five revisions are kept per entity translation.

## Configuration

This can be configured in code with:
```php

# public/sites/default/*.settings.php
$config['helfi_api_base.delete_revisions']['entity_types'] = [
  'node',
  'paragraph',
  'tpr_unit',
  'tpr_service',
  'tpr_errand_service',
];
```

or by creating a configuration yml file:

```yaml
# conf/cmi/helfi_api_base.delete_revisions.yml
entity_types:
  - node
  - paragraph
  - tpr_unit
  - tpr_service
  - tpr_errand_service
```

## Usage

All configured entities are queued for clean up on entity save.

To clean up existing entities at once, run: `drush helfi:revision:delete {entity_type}`.

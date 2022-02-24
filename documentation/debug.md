# Debug plugin

By default, this module provides the following debug plugins:
- `composer`: Shows installed `drupal/helfi_*` and `drupal/hdbt*` packages and their versions.
- `migrate`: List of various data about migration status, such as `last_imported` timestamp and `status`.

Navigate to `/admin/debug` to see available debug data, or`/api/v1/debug` for JSON endpoint.

## Creating your own debug data provider plugin

See [/src/Plugin/DebugDataItem/Composer.php](src/Plugin/DebugDataItem/Composer.php) for an example plugin implementation.

At minimum, you need:
- A plugin class that implements `\Drupal\helfi_debug\DebugDataItemInterface`
- Create a plugin specific template (`debug-item--{plugin_id}.html.twig`). See [/templates/debug-item.html.twig](templates/debug-item.html.twig) for more information.

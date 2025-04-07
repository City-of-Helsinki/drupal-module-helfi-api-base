# Debug plugin

Debug plugins are used to monitor the internal state of the Drupal instance.
THe plugins implement `/api/v1/debug/{plugin_id}` route, which returns 503, if
the plugin check fails. The plugins can provide additional context, which is
visible at `/admin/debug` page.

By default, this module provides the following debug plugins:

| Plugin             | Description                                                                                                         | Check fails                 |
|--------------------|---------------------------------------------------------------------------------------------------------------------|-----------------------------|
| `composer`         | Shows installed `drupal/helfi_*` and `drupal/hdbt*` packages and their versions. The check never fails.             | Never fails.                |
| `migrate`          | List of various data about migration status, such as `last_imported` timestamp and `status`. The check never fails. | Never fails.                |
| `maintenance_mode` | Shows if the site is in maintenance mode.                                                                           | The maintenance mode is on. |

## Creating your own debug data provider plugin

See [src/Plugin/DebugDataItem/Composer.php](/src/Plugin/DebugDataItem/Composer.php) for an example plugin implementation.

At minimum, you need:
- A plugin class that implements `\Drupal\helfi_debug\DebugDataItemInterface`

Optionally, you may add:
- A plugin specific template (`debug-item--{plugin_id}.html.twig`). See [templates/debug-item.html.twig](/templates/debug-item.html.twig) for more information. This template is drawn on `/admin/debug` page.

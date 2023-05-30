# Drupal base

![CI](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/workflows/CI/badge.svg) [![codecov](https://codecov.io/gh/City-of-Helsinki/drupal-module-helfi-api-base/branch/main/graph/badge.svg?token=P6CG4IIAO9)](https://codecov.io/gh/City-of-Helsinki/drupal-module-helfi-api-base)

Base module for `drupal-helfi-platform` ecosystem.

## Requirements

- PHP 8.1 or higher

## Features

- [API user manager](documentation/api-accounts.md): Allows API users to be created/managed from an environment variable.
- [Automatic external cache invalidation](documentation/automatic-external-cache-invalidation.md): Invalidate caches from external projects using [PubSub messaging](documentation/pubsub-messaging.md) service.
- [Debug collector](documentation/debug.md): A plugin to collect and show various debug information in one place.
- [Deploy hooks](documentation/deploy-hooks.md): Allows custom tasks to be defined that are run before or after deployment.
- [Environment resolver](documentation/environment-resolver.md): A service to fetch metadata for given project.
- [Default language resolver](documentation/default-languages.md): A service to handle default primary languages and language fallbacks.
- [Logging](documentation/logging.md): Log to Docker container stdout.
- [Link text filter](documentation/link.md): A custom `filter` plugin to scan and parse external links
- [Migrate](documentation/migrate.md): Various features to help with migrations.
- [PO Importer](documentation/po-importer.md): Allows modules to define translations that can be imported using Drush.
- [PubSub messaging](documentation/pubsub-messaging.md): A PubSub message service to send/receive messages.
- [Remote Entity](documentation/remote-entity.md): A base entity to be used with migrations.
- [Testing](documentation/testing.md): Various features to help with automated testing.

## Contact

Slack: #helfi-drupal (http://helsinkicity.slack.com/)

Mail: `drupal@hel.fi`

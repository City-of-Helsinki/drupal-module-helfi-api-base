# Drupal base

![CI](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/workflows/CI/badge.svg) [![codecov](https://codecov.io/gh/City-of-Helsinki/drupal-module-helfi-api-base/branch/main/graph/badge.svg?token=P6CG4IIAO9)](https://codecov.io/gh/City-of-Helsinki/drupal-module-helfi-api-base)

A base module for [drupal-helfi-platform](https://github.com/City-of-Helsinki/drupal-helfi-platform) ecosystem. Contains various features used in other custom modules.

## Requirements

- PHP 8.1 or higher

## Features

- [API user manager](documentation/api-accounts.md): Allows API users to be created/managed from an environment variable.
- [API client](documentation/api-client.md): Services for caching and mocking http responses.
- [Automatic external cache invalidation](documentation/automatic-external-cache-invalidation.md): Invalidate caches from external projects using [PubSub messaging](documentation/pubsub-messaging.md) service.
- [Automatic revision deletion](documentation/revisions.md): Clean up old entity revisions automatically.
- [Debug collector](documentation/debug.md): A plugin to collect and show various debug information in one place.
- [Default language resolver](documentation/default-languages.md): A service to handle default primary languages and language fallbacks.
- [Deploy hooks](documentation/deploy-hooks.md): Allows custom tasks to be run before or after deployment.
- [Disable user password](/documentation/disable-user-password.md): A deployment hook to prevent users from logging in using password.
- [Disable email sending](/documentation/disable-email-sending.md): Sending email is disabled by default.
- [Environment resolver](documentation/environment-resolver.md): A service to fetch metadata for given project.
- [Feature toggle](/documentation/feature-toggle.md): Allow certain functionality to be toggled on/off.
- [Logging](documentation/logging.md): Log to Docker container stdout.
- [Link text filter](documentation/link.md): A custom `filter` plugin to scan and parse external links
- [Migrate](documentation/migrate.md): Various features to help with migrations.
- [PO Importer](documentation/po-importer.md): Allows modules to define translations that can be imported using Drush.
- [PubSub messaging](documentation/pubsub-messaging.md): A PubSub message service to send/receive messages.
- [Remote Entity](documentation/remote-entity.md): A base entity to be used with migrations.
- [Testing](documentation/testing.md): Various features to help with automated testing.
- [User expire](/documentation/user-expire.md): Block inactive accounts automatically.

## Contact

Slack: #helfi-drupal (http://helsinkicity.slack.com/)

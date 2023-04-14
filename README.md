# Drupal base

![CI](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/workflows/CI/badge.svg) [![codecov](https://codecov.io/gh/City-of-Helsinki/drupal-module-helfi-api-base/branch/main/graph/badge.svg?token=P6CG4IIAO9)](https://codecov.io/gh/City-of-Helsinki/drupal-module-helfi-api-base)

Base module for `drupal-helfi-platform` ecosystem.

## Requirements

- PHP 8.0 or higher

## Features

- [Environment API accounts](documentation/api-accounts.md): Allows API accounts to be mapped from an environment variable.
- [Debug collector](documentation/debug.md): A plugin to collect and show various debug information in one place.
- [Deploy hooks](documentation/deploy-hooks.md): Allows custom tasks to be defined that are run before or after deployment.
- [Environment resolver](documentation/environment-resolver.md): A service to fetch metadata for given project.
- [Logging](documentation/logging.md): Log to Docker container stdout.
- [Link text filter](documentation/link.md): A custom `filter` plugin to scan and parse external links
- [Migrate](documentation/migrate.md): Various features to help with migrations.
- [PO Importer](documentation/po-importer.md): Allows modules to define translations that can be imported using Drush.
- [Remote Entity](documentation/remote-entity.md): A base entity to be used with migrations.
- [Testing](documentation/testing.md): Various features to help with automated testing.

## Contact

Slack: #helfi-drupal (http://helsinkicity.slack.com/)

Mail: `drupal@hel.fi`

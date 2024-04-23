# Logging

Errors caught by Drupal will be logged to `php://stdout` by `drupal/monolog` module.

JSON parsing needs to be enabled manually in order for this to work. See https://helsinkisolutionoffice.atlassian.net/wiki/spaces/HELFI/pages/7854817294/Logging+Structured+logs#Configuration-using-openshift-console.

## Usage

Enable `monolog` module: `drush en monolog`.

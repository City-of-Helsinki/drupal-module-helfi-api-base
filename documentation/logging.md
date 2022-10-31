# Logging

Errors caught by Drupal will be logged to `temporary://drupal.log` file as JSON, which will be piped to container stdout by [15-syslog.sh](https://github.com/City-of-Helsinki/drupal-docker-images/blob/main/openshift/drupal/files/entrypoints/15-syslog.sh) docker entrypoint.

JSON parsing needs to be enabled manually in order for this to work. See https://helsinkisolutionoffice.atlassian.net/wiki/spaces/HELFI/pages/7854817294/Logging+Structured+logs#Configuration-using-openshift-console.

## How to disable logging

Set `helfi_api_base.logger_enabled` service parameter to `false`:

```
# public/sites/default/services.yml
# public/sites/default/{env}.services.yml
parameters:
  helfi_api_base.logger_enabled: false
```

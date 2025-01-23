## Asset versioning

Helfi api base replaces `HELFI_DEPLOYMENT_IDENTIFIER` in libraries.yml files with string that is unique for each release. This can be used in theme or module libraries.yml files to automatically bust caches when dependencies are updated.

Example:
```
my-library:
  version: HELFI_DEPLOYMENT_IDENTIFIER
  js:
    dist/js/my-library.min.js: {
      preprocess: false,
      minified: true
    }
  dependencies:
    - core/drupalSettings
    - core/drupal
```

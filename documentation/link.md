# Link

## Link filter

You can enable the filter from `Configuration -> Text formats and editors -> Configure -> Enable the Hel.fi: Link converter filter`. This must be run after `Convert URLs into links` filter if enabled.

The filter parses all links from markup fields and runs them through `#type => link` render element, so they can be processed the same way as other links are processed. See [src/Plugin/Filter/LinkConverter.php](https://github.com/City-of-Helsinki/drupal-module-helfi-api-base/blob/main/src/Plugin/Filter/LinkConverter.php).

## Link preprocessor

We override the default link `#preprocess` callback in [helfi_api_base_element_info_alter()](/helfi_api_base.module) hook to run all our links through a template to figure out whether the link is external or not. See:
- [src/Link/LinkProcessor.php](/src/Link/LinkProcessor.php)
- [src/Helper/ExternalUri.php](/src/Helper/ExternalUri.php)
- [tests/themes/link_template_test_theme/templates/helfi-link.html.twig](/tests/themes/link_template_test_theme/templates/helfi-link.html.twig) (this is overridden in `hdbt` theme as well).


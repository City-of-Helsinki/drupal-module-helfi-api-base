# Default language resolver

`helfi_api_base.default_language_resolver` service provides a way to handle default primary languages and language fallbacks.

Certain languages have standard and full support (usually en, fi and sv) and have all navigation elements localized. Some languages are not fully localized and use a fallback language for content and elements. This service provides a way to configure and detect these languages.

## Parameters

The default languages and the fallback language are defined in `helfi_api_base.services.yml`. These can be overridden on a project basis in the project's own services.yml files.

`parameters.helfi_api_base.default_languages` is a list of language codes for languages that have standard support. All other languages will use the language code defined in `parameters.helfi_api_base.fallback_language` as a fallback.

## Usage

```php
/** @var \Drupal\helfi_api_base\Language\DefaultLanguageResolver $language_resolver */
$language_resolver = \Drupal::service('helfi_api_base.default_language_resolver');

// Get list of default language IDs.
$language_resolver->getDefaultLanguages();

// Get fallback langcode.
$language_resolver->getFallbackLanguage();

// Check if current language is not considered default and should use fallbacks.
$language_resolver->isAltLanguage();

// Get current langcode or fallback language.
$language_resolver->getCurrentOrFallbackLanguage();

// Get lang and dir attributes for default or fallback language.
$language_resolver->getFallbackLangAttributes();
$language_resolver->getCurrentLangAttributes();

```

## Twig

The current language object, the alternative language boolean and possible fallback language attributes are added to each template via `helfi_api_base_template_preprocess_default_variables_alter()`. 

### Usage

```twig
{# Get the language ID or "langcode" #}
{{ language.id }}

{# Get the language human readable name #}
{{ language.name }}

{# Check for alternative language. (true|false) #}
{{ alternative_language }}

{# Get the alternative language fallback attributes. #}
{% set lang = lang_attributes.fallback_lang %}
{% set dir = lang_attributes.fallback_dir %}
```

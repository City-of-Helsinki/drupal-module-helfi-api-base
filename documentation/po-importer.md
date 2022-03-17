# PO Importer

PO Importer can be used to override translations. 

The translations are imported with `customized = 1` boolean, in `locales_target` table. 
The customized variable is a boolean indicating whether the translation is custom to this site.

Create `translations/override/{langcode}.po` files inside your module (like `translations/override/fi.po`, `translations/override/sv.po`).

See https://www.drupal.org/community/contributor-guide/reference-information/localize-drupal-org/working-with-offline/po-and

Run `drush helfi:locale-import {module_name}`.

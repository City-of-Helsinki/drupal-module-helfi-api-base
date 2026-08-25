#Sentry errors

## Sentry options alter event subscriber

Use SentryOptionsAlterEventSubscriber to alter or ignore errors sent to Sentry

### Fingerprint rules

Sentry uses fingerprints to group similar errors under one item.

### Ignored errors

Uses str_contains to decide whether to send the error to Sentry or not.
Add a part of the error message to the ignoredErrors-array to ignore the error.

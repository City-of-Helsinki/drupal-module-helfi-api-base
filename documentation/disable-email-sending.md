# Disable email sending

Email sending is disabled by default in `helfi_api_email_alter()` hook.

To enable email sending, set `disable_email_sending` setting to false:

```yaml
# conf/cmi/helfi_api_base.features.yml
disable_email_sending: false
```

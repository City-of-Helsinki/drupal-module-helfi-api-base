# User expire

## Settings

User blocking and deletion are controlled by separate feature flags:

```yaml
# conf/cmi/helfi_api_base.features.yml
user_expire: true
user_delete: true
```

Set either to `false` to disable that feature independently.

## User expire

When `user_expire` is enabled, accounts that have been inactive for longer than 6 months are blocked automatically.

## User delete

When `user_delete` is enabled, accounts that have been inactive for longer than five years are deleted automatically. This works independently of `user_expire`.

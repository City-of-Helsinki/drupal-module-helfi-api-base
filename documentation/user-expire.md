# User expire

## Settings

This feature can be disabled by changing `user_expire` setting to false:
```yaml
# conf/cmi/helfi_api_base.features.yml
user_expire: false
```

## User expire

Accounts that have been inactive for longer than 6 months are blocked automatically.

## User delete

Blocked accounts that have been inactive for longer than five years are deleted automatically.

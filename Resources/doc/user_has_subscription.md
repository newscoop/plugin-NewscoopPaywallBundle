Checks if user has valid subscription and can access content.
=====================

This smarty modifier to check if user has valid subscription and can check content
for which he/she subscribed.

##Usage:

```

{{ if $gimme->user|has_subscription }}
	// can access content
{{ /if }}
```

Modifier return true, if user has valid subscription, otherwise returns false.

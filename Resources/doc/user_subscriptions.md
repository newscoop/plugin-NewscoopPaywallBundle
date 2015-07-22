Listing user subscriptions
=====================

This smarty function displays all user subscriptions.

##Usage:

```

{{ list_user_subscriptions language="ru" }}
	{{$gimme->user_subscription|dump }}
{{ /list_user_subscriptions }}
```

- ***language*** parameter specifies which translations should be fetched for subscription name and description.
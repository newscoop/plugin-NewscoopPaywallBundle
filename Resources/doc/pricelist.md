Price list:
=====================

This smarty function displays all available subscriptions to create price list.

##Usage:

```
{{ list_pricetable }}
    {{ $gimme->subscription->name }} // Subscription name
    {{ $gimme->subscription->price }} // Subscription price
    {{ $gimme->subscription->description }} // Subscription description
    {{ $gimme->subscription->range }} // Subscription range in days
    {{ $gimme->subscription->type }} // Subscription type (e.g. publication)
    {{ $gimme->subscription->currency }} // Subscription currency (e.g. EUR)
{{ /list_pricetable }}
```

##List constraints are:
- name (e.g. `{{ list_pricetable constraints="name is Test" }}`)

##List order:
- orderBy (e.g. `{{ list_pricetable order="created_at asc" }}`)
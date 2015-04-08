Price list:
=====================

This smarty function displays all available subscriptions to create price list.

##Usage:

```
{{ list_pricetable }}
	{{ $gimme->subscription->identifier }} // Subscription identifier
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
- order (e.g. `{{ list_pricetable order="created_at asc" }}`)

## Subscribing for a given subscription (in case of only one subscription)

```
<form action="{{ generate_url route='paywall_subscribe' }}">
{{ list_pricetable }}
	<input type="radio" name="subscription_name" value="{{ $gimme->subscription->name }}" />
	Name: {{ $gimme->subscription->name }}
	Price: {{ $gimme->subscription->price }}
{{ /list_pricetable }}
<button type="submit">Submit</button>
</form>
```

Copy [`_paywall`](https://github.com/newscoop/plugin-NewscoopPaywallBundle/tree/master/Resources/_paywall) folder to your themes main directory and customize its templates for your needs.
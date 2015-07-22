Price list:
=====================

This smarty function displays all available subscriptions to create price list.

##Usage:

```
 <table class="table table-striped table-bordered">
    <tr>
        <td>Name of subscription</td>
        <td>Price</td>
        <td>Duration of subscription</td>
        <td>Description</td>
    </tr>
    {{ list_pricetable }}
    <tr>
        <td>{{ $gimme->subscription->name }} </td>
        <td>{{ $gimme->subscription->price }} {{ $gimme->subscription->currency }} </td>
        <td>
        <select name="">
        <option value="">-- choose ---</option>
            {{foreach from=$gimme->subscription->ranges item=val}}
                <option value="{{$val.id}}">{{$val.value}} {{ if $val.attribute == 'month' }} month(s){{ /if}}</option>
            {{/foreach}}
        </select>
        </td>
        <td>{{ $gimme->subscription->description }} </td>
    </tr>
    {{ /list_pricetable }}
</table>
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
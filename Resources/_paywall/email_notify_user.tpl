{{dynamic}}
Hi {{$user->uname}}!<br><br>

<b>Your order items:</b> <br><br>
{{foreach from=$userSubscriptions item=userSubscription}}
Name: {{ $userSubscription->name }} <br>
To pay: {{ $userSubscription->price }} {{ $userSubscription->currency }} <br>
Publication: {{ $userSubscription->publication }} <br>
Status: {{ if $userSubscription->is_active }} Active {{ else }} Inactive {{ /if }} <br>
<br></br>
{{/foreach}}

Total items: {{ $order->getItemsTotal() }} {{ $order->getCurrency() }}</br>
Discount total: {{ $order->getDiscountTotal() }} {{ $order->getCurrency() }}</br>
Total: {{ $order->getTotal() }} {{ $order->getCurrency() }}</br>

{{ set_placeholder subject="Paywall: You have ordered a new subscription!" }}
{{/dynamic}}
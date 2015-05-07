{{dynamic}}
Hi {{$user->uname}}!<br><br>

One of the staff members at "{{ $userSubscription->publication }}" created subscription for you. <br><br>
<b>Your subscription details are:</b><br>
Name: {{ $userSubscription->name }} <br>
To pay: {{ $userSubscription->price }} {{ $userSubscription->currency }} <br>
Publication: {{ $userSubscription->publication }} <br>
Status: {{ if $userSubscription->is_active }} Active {{ else }} Inactive {{ /if }} <br>

{{ set_placeholder subject="Paywall: Subscription has been created!" }}
{{/dynamic}}
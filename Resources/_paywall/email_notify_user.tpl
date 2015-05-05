{{dynamic}}
Hi {{$user->uname}}!<br><br>

You have successfully ordered a new subscription. <br><br>
<b>Your subscription details are:</b><br>
Name: {{ $userSubscription->name }} <br>
To pay: {{ $userSubscription->price }} {{ $userSubscription->currency }} <br>
Publication: {{ $userSubscription->publication }} <br>
Status: {{ if $userSubscription->is_active }} Active {{ else }} Inactive {{ /if }} <br>

{{ set_placeholder subject="Paywall: You have ordered a new subscription!" }}
{{/dynamic}}
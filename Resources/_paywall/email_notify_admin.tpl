{{dynamic}}
User <b>{{$user->uname}}</b> ordered a new subscription plan.<br><br>

<b>Subscription details:</b><br>

Name: {{ $userSubscription->name }} <br>
To pay: {{ $userSubscription->price }} {{ $userSubscription->currency }} <br>
Publication: {{ $userSubscription->publication }} <br>
Status: {{ if $userSubscription->is_active }} Active {{ else }} Inactive {{ /if }} <br>

{{ set_placeholder subject="Paywall: New subscription order by {{$user->uname}}!" }}
{{/dynamic}}
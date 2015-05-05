{{ dynamic }}
Hi {{ $user->uname }}!<br><br>

Your subscription has been {{ if $userSubscription->is_active }} activated {{ else }} deactivated {{ /if }}! See subscription details below.<br><br>

<b>Your subscription details are:</b><br>
Name: {{ $userSubscription->name }} <br>
To pay: {{ $userSubscription->price }} {{ $userSubscription->currency }} <br>
Publication: {{ $userSubscription->publication }} <br>
Status: {{ if $userSubscription->is_active }} Active {{ else }} Inactive {{ /if }} <br>
{{ if $userSubscription->is_active }}Expires at: {{$userSubscription->expiration_date|date_format:'%Y-%m-%d %H:%M:%S'}}{{ /if }}

{{ set_placeholder subject="Paywall: Your subscription has been {{ if $userSubscription->is_active }} activated {{ else }} deactivated {{ /if }}!" }}
{{/dynamic}}

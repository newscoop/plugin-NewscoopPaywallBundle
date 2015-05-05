{{ dynamic }}
Dear {{ $user->uname}},<br>
This is an automatically generated e-mail message.<br>
Your {{ $userSubscription->name }} subscription (started on {{ $userSubscription->start_date|date_format:'%Y-%m-%d %H:%M:%S'  }}) for publication "{{ $userSubscription->publication }}" {{ if $userSubscription->expiration_date }}will expire on {{ $userSubscription->expiration_date|date_format:'%Y-%m-%d %H:%M:%S' }} (in {{ $userSubscription->expire_in_days }} days).{{ /if }}<br>
Please renew your subscription.<br>
{{ set_placeholder subject="Paywall: Your subscription will expire soon!" }}
{{ /dynamic }}
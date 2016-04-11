<h2>Total:</h2>
<span>{{ $amount }} {{ $currency }}</span>

<h2>Payment Methods:</h2>

<form action="/paywall/purchase/" method="post" accept-charset="utf-8">
	<input type="radio" name="paymentMethod" value="offline"/>Offline<br>
	<input type="radio" name="paymentMethod" style="float:left" value="PayPal_Express"/><img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left" style="margin-right:7px;"><br><br><br><br>
	<button type="submit">I order and pay</button>
</form>

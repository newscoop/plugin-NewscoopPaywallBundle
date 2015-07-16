API endpoints
=====================

Plugin provides four API endpoints to retrieve data about the subscriptions.

**Note:** By default all Paywall API endpoints need to public. This can be changed in Newscoop Admin Panel (`Configure -> API -> Public Resources`).

**Endpoints:**

- Pricelist (list all defined subscriptions with periods, currencies and prices)

```
GET: /api/paywall/pricelist/{currency}/{locale}

Parameters:
- currency - string value (max 3 letters) e.g. EUR, pln
- locale - string value e.g. en, pl

```

- My Subscriptions (list current user's subscriptions)

```
GET: /api/paywall/my-subscriptions/{locale}

Parameters:
- locale - string value e.g. en, pl

```

- Discounts (list all discounts available in the Paywall)

```
GET: /api/paywall/discounts

```

- Currencies (list all currencies available in the Paywall)

```
GET: /api/paywall/currencies

```
NewscoopPaywallBundle
=====================

NewscoopPaywallBundle - add paywalls support for newscoop subscriptions.


## Sample application/configs/subscriptions/subscriptions.yml configuration 

```
subscriptions: # inform system about subscription definitions
    specified_article: # name of your subscription definition
        type: article # type of subscription (publication/issue/section/article)
        range: 30 # how many days
        price: 20 # price for subscription
        currency: PLN # price currency
        specify: # you can specify element for that definition - everything there is optional
            publication: 2 # specify publication id
            issue: 13 # specify issue id
            section: 10 # specify section id
            article: 101 # specify article id
    articles_from_publication:
        type: article
        range: 30
        price: 15
        currency: PLN
        specify:
            publication: 1
    article:
        type: article
        range: 30
        price: 10
        currency: PLN
paypal_config: # inform system about paypal config
    seller_email: mikolajczuk.private@gmail.com # your paypal account name
    item_name_format: "Subscription on %publication_name%" # Define name of product: "Subscription on The New Custodian"
    test_seller_email: mikolajczuk.private@gmail.com # your paypal test account name
```

## For good user experience you will need to modify and create few templates. In our examples we will based on "Quetzal" Newscoop theme.

### article.tpl - change lines where you show article content

```
{{ $subscription = $gimme->user->subscriptions->has_article($gimme->article->number) }}
{{ if !$subscription || !$subscription->is_active }}
    {{ subscribe_form }}{{ /subscribe_form }}
{{ else }}
    {{ if $gimme->article->type_name == "debate" }}
        {{ include file="_tpl/article-debate.tpl" }}
    {{ else }}
        {{ include file="_tpl/article-cont.tpl" }}
    {{ /if }}
{{ /if }}
```                

### newscoop_paywall_get.tpl

```
{{ include file="_tpl/_html-head.tpl" }}

<body>
<!--[if lt IE 7]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
<![endif]-->
          
{{ include file="_tpl/header.tpl" }}

<section role="main" class="internal-page">
    <div class="wrapper">
        <div class="container">
            <section id="content">
                <div class="row">
                {{ block content }}
                    {{ paypal_payment_form subscriptionId=$subscriptionId test=true }} {{ /paypal_payment_form }}
                {{ /block }}     
                </div> <!--end div class="row"-->
            </section> <!-- end section id=content -->
        </div> <!-- end div class='container' -->
    </div> <!-- end div class='wrapper' -->
</section> <!-- end section role main -->

{{ include file="_tpl/footer.tpl" }}

{{ include file="_tpl/_html-foot.tpl" }}
```

###  newscoop_paywall_success.tpl - page with success message (payment was accepted - wait for confirmation from provider (or newspaper - not implemented yet))

```
{{ include file="_tpl/_html-head.tpl" }}

<body>
<!--[if lt IE 7]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
<![endif]-->
          
{{ include file="_tpl/header.tpl" }}

<section role="main" class="internal-page">
    <div class="wrapper">
        <div class="container">
            <section id="content">
                <div class="row">
                {{ block content }}
                    <h3>success</h3>
                {{ /block }}     
                </div> <!--end div class="row"-->
            </section> <!-- end section id=content -->
        </div> <!-- end div class='container' -->
    </div> <!-- end div class='wrapper' -->
</section> <!-- end section role main -->

{{ include file="_tpl/footer.tpl" }}

{{ include file="_tpl/_html-foot.tpl" }}
```

###  newscoop_paywall_cancel.tpl - page with message about canceled payment (user/provider canceled payment)

```
{{ include file="_tpl/_html-head.tpl" }}

<body>
<!--[if lt IE 7]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
<![endif]-->
          
{{ include file="_tpl/header.tpl" }}

<section role="main" class="internal-page">
    <div class="wrapper">
        <div class="container">
            <section id="content">
                <div class="row">
                {{ block content }}
                    <h3>cancel</h3>
                {{ /block }}     
                </div> <!--end div class="row"-->
            </section> <!-- end section id=content -->
        </div> <!-- end div class='container' -->
    </div> <!-- end div class='wrapper' -->
</section> <!-- end section role main -->

{{ include file="_tpl/footer.tpl" }}

{{ include file="_tpl/_html-foot.tpl" }}
```

###  newscoop_paywall_error.tpl - page with error message (something was broken)

```
{{ include file="_tpl/_html-head.tpl" }}

<body>
<!--[if lt IE 7]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
<![endif]-->
          
{{ include file="_tpl/header.tpl" }}

<section role="main" class="internal-page">
    <div class="wrapper">
        <div class="container">
            <section id="content">
                <div class="row">
                {{ block content }}
                    <h3>error</h3>
                {{ /block }}     
                </div> <!--end div class="row"-->
            </section> <!-- end section id=content -->
        </div> <!-- end div class='container' -->
    </div> <!-- end div class='wrapper' -->
</section> <!-- end section role main -->

{{ include file="_tpl/footer.tpl" }}

{{ include file="_tpl/_html-foot.tpl" }}
```
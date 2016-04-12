Newscoop Paywall Bundle
=====================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/newscoop/plugin-NewscoopPaywallBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/newscoop/plugin-NewscoopPaywallBundle/?branch=master)
[![Code Climate](https://codeclimate.com/github/newscoop/plugin-NewscoopPaywallBundle/badges/gpa.svg)](https://codeclimate.com/github/newscoop/plugin-NewscoopPaywallBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b86606ea-4910-4f65-bef0-32a0efc07b30/mini.png)](https://insight.sensiolabs.com/projects/b86606ea-4910-4f65-bef0-32a0efc07b30)

Newscoop Paywall Bundle is a simple and powerful plugin that allows online publishers to monetize their content.

Features:
=====================

- creating publication, issue, section, article aware subscriptions,
- email notifications,
- ordered subscription management,
- discounts (supports percentage discounts at the moment),
- possibility to define subscription duration/period (in months and days),
- supports diffrent currencies
- renewals
- imports live exchange rates from European Central Bank and Central Bank of Azerbaijan
- partial support for subscriptions' translations (subscription name and description is translatable)
- API
- smarty functions (see `Samples` section below)
- integrates a framework agnostic, multi-gateway payment processing library called [Omnipay](https://github.com/thephpleague/omnipay)

This plugin realizes "offline" as well as "online" payments. By default, there is a [PayPal integration](https://github.com/thephpleague/omnipay-paypal) built-in.


Installing Newscoop Paywall Plugin Guide
-------------
Installation is a quick process:


1. Installing plugin through our Newscoop Plugin System
2. Import currencies
3. Configure PayPal gateway (optional)
4. That's all!

### Step 1: Installing plugin through our Newscoop Plugin System
Run the command:
``` bash
$ php application/console plugins:install "newscoop/newscoop-paywall-bundle"
$ php application/console assets:install public/
```
Plugin will be installed to your project's `newscoop/plugins/Newscoop` directory.

### Step 2: Import currencies

To make use of the paywall and to be able to create new subscriptions, currencies needs to be defined.
This can be done manually as well as automatically, we recommend the second option.

Run command:

``` bash
$ php application/console paywall:currency:import
```

Currencies will be imported from [European Central Bank](https://www.ecb.europa.eu) and default used currency will be EUR.

**Note:**

You can also import currencies from the [Central Bank of Azerbaijan](http://en.cbar.az/) where the default currency will be AZN.

To do that run command:

``` bash
$ php application/console paywall:currency:import cbar
```

### Step 3: Configure PayPal gateway (optional)

After the installation, by default the plugin realizes "offline" payments.
If you want to use built-in PayPal integration, you will need to provide more details to access your PayPal account.

To do this you need to add the following parameters to your `custom_parameters.yml` file in Newscoop, with your PayPal credentials:

```yaml
# application/configs/parameters/custom_parameters.yml
parameters:
    paywall_omnipay:
        brandName: "My website" # this will show up in PayPal payment step as a brand name.
        gateways:
            PayPal_Express:
                username: <api_username>
                password: <api_password>
                signature: <api_signature>
                # test_mode: true #used for testing purposes when using PayPal sandbox
```

Check [here](https://developer.paypal.com/docs/classic/api/apiCredentials/#creating-an-api-signature) how to obtain PayPal credentials for your account.


### Step 4: That's all!
Go to Newscoop Admin panel and then hit `Plugins` tab. Newscoop Paywall Plugin will show up there. Now, you can add new subscriptions etc.


Samples:
-------
```
Resources/doc/
```

License
-------

This bundle is under the GNU General Public License v3. See the complete license in the bundle:

    LICENSE.txt

About
-------
Newscoop Paywall Bundle is a [Sourcefabric z.ú.](https://github.com/sourcefabric) initiative.

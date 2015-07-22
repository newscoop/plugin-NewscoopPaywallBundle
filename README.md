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
- possibility to define subscription duration/period (supports `months` option at the moment),
- supports diffrent currencies
- renewals
- imports live exchange rates from Central European Bank and Central Bank of Azerbaijan
- partial support for subscriptions' translations (subscription name and description is translatable)
- API
- smarty functions (see `Samples` section below)

This plugin realizes only "offline" payments, there is no payment gateways implementation yet, which can be used at the moment. However, there is a PayPal integration built-in.

In the future, this plugin will integrate a framework agnostic, multi-gateway payment processing library called [Omnipay](https://github.com/thephpleague/omnipay).

Installing Newscoop Paywall Plugin Guide
-------------
Installation is a quick process:


1. Installing plugin through our Newscoop Plugin System
2. That's all!

### Step 1: Installing plugin through our Newscoop Plugin System
Run the command:
``` bash
$ php application/console plugins:install "newscoop/newscoop-paywall-bundle"
$ php application/console assets:install public/
```
Plugin will be installed to your project's `newscoop/plugins/Newscoop` directory.


### Step 2: That's all!
Go to Newscoop Admin panel and then hit `Plugins` tab. Newscoop Paywall Plugin will show up there.


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

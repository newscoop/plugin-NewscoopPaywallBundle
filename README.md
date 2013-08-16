NewscoopPaywallBundle
=====================

NewscoopPaywallBundle is a simple, powerful plugin that allows online publishers to sell access to their content of the publications, issues, sections, articles.

Features:
=====================
###Define and add new subscriptions by:
- type (`Publication`, `Issue`, `Section`, `Article`)
- price
- duration
- currency

###Add subscription specification by defined subscription type

###Manage defined subscriptions
 * delete subscriptions
 * edit subscriptions
- search defined subscriptions by:
    - user
    - subscription type (publication, issue, section, article)
    - duration in days
    - price
    - currency

###Management of existing user subscriptions
 * assignee existing subscriptions to individual users
 * activate/deactivate user subscriptions
 * edit defined user subscription
 * view subscription details while adding a user subscription
- search user subscriptions by:
    - username
    - publication name
    - to pay amount
    - currency
    - status (`Active`/`Deactivated`)
    - payment type (`Paid`, `Paid now`, `Trial`)

###Specification management of the user subscription
- add Issues, Sections, Articles to each user-defined subscriptions by:
    - Individual languages (show all `Issues`|`Sections`|`Articles` by publication language)
    - Regardless of the languages (show all `Issues`|`Sections`|`Articles` diffrent than publication language)
    - start date
    - paid days
    - days
- edit all the specifications (`Issues`/`Sections`/`Articles`) of the user subscriptions by:
    - start date
    - paid days
    - days
- edit single specification
- delete specifications

###Paywall configuration
- choose one of available payment service providers:
    - Paypal is supported only at the moment (we will add more providers soon)

Installing Newscoop Paywall Plugin Guide
-------------
Installation is a quick process:


1. Installing plugin through our Newscoop Plugin System
2. That's all!

### Step 1: Installing plugin through our Newscoop Plugin System
Run the command:
``` bash
$ php application/console plugins:install "newscoop/newscoop-paywall-bundle" --env=prod
```
Plugin will be installed to your project's `newscoop/plugins/Newscoop` directory.


### Step 2: That's all!
Go to Newscoop Admin panel and then hit `Plugins` tab. Newscoop Paywall Plugin will show up there.


Sample:
=====================
```
Resources/doc/
```

License
-------

This bundle is under the GNU General Public License v3. See the complete license in the bundle:

    LICENSE.txt

About
-------
NewscoopPaywallBundle is a [Sourcefabric o.p.s](https://github.com/sourcefabric) initiative.
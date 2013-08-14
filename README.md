NewscoopPaywallBundle
=====================
NewscoopPaywallBundle - add paywall support for Newscoop subscriptions.

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
    - Individual languages (ex. all sections by publicaton language)
    - Regardless of the languages (ex. all sections diffrent than publication language)
- edit all the specifications (`Issues`/`Sections`/`Articles`) of the user subscriptions by:
    - start date
    - paid days
    - days
- edit single specification
- delete specification of the user subscriptions

Sample:
=====================
```
Resources/doc/
```
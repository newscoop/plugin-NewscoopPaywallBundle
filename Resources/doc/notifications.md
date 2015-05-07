Email Notifications
=====================

There are 5 types of notifications emails:

1. Notification about newly created subscription, which will be sent to provided email address, configurable in the Paywall Backend. (
`Paywall -> Configure Paywall -> Notifications email field`). See `Resources/_paywall/email_notify_admin.tpl`.
2. Notification about newly created subscription, which will be sent to the user who subscribed. See `Resources/_paywall/email_notify_user.tpl`.
3. Notification when subscription will be deactivated/activated through the Paywall admin panel (
`Paywall -> Manage User Subscriptions -> Activate/Deactivate button`). See `Resources/_paywall/email_subscription_status.tpl`.
4. Notification about expiring subscription (this notification is being sens by cron job). See `Resources/_paywall/email_subscription_expiration.tpl`.
5. Notification about subscription created by publication staff. In Paywall admin panel, subscription can be created for every user. If admin will create subscription for one of the available users, notification will be sent to that user, with the informations about created subscription. See `Resources/_paywall/email_subscription_admin_created.tpl`.


##Usage:

Notifications can be enabled/disabled via Paywall backend (`Paywall -> Configure Paywall -> Enable email notifications checkbox`).
In Paywall configuration there is also field called "Sender email" - all notifications will be sent from this email. By default this email is set to the email address provided during the Newscoop installation process.

Copy [`_paywall`](https://github.com/newscoop/plugin-NewscoopPaywallBundle/tree/master/Resources/_paywall) folder to your themes main directory and customize its templates for your needs.
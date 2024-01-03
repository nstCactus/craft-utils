# Craft-utils

This is a collection a Craft utilities to ease custom development on Craft CMS.


## `AbstractModule`

This base class for custom modules aims at making less painful to create a Craft
module.
Most of the time, all you have to do to register/customize the following
components is to override the corresponding getter:
  - translation category (reasonable default value provided)
  - CP template roots (reasonable default value provided)
  - site template roots (reasonable default value provided)
  - twig extensions
  - CP nav items
  - CP routes
  - site routes
  - User permissions
  - Craft variables additions
  - element types
  - view hooks

⚠️ There may be some performance implications as the getters are executed on
each request. Do as little as possible in the getters, and return early if
possible.

If you need to further optimize your code, it's easy to get rid of this module
and register the components the traditional way.


## `MailerComponentConfiguratorModule`

This simple module lets you configure the Craft mailer component using environment variables.

Set the mailer transport adapter using the `NST_MAILER_TRANSPORT_TYPE` environment variable.
Supported values: `smtp`, `mailchimp` (`mandrill` is also accepted, but it is deprecated) or `mailjet`.

Depending on the adapter you choose, different environments variables are needed to configure the adapter.

### Common to all adapters

- `NST_MAILER_FROM_NAME`: required — the name used in the `From:` header of outgoing emails
- `NST_MAILER_FROM_EMAIL`: required — the email used in the `From:` header of outgoing emails

### SMTP

- `NST_MAILER_SMTP_HOST`: required — the SMTP server hostname
- `NST_MAILER_SMTP_PORT`: required — the SMTP server port number
- `NST_MAILER_SMTP_USE_AUTHENTICATION`: boolean — whether the SMTP server requires authentication
- `NST_MAILER_SMTP_USERNAME`: required if `NST_MAILER_SMTP_USE_AUTHENTICATION` — the SMTP username
- `NST_MAILER_SMTP_PASSWORD`: required if `NST_MAILER_SMTP_USE_AUTHENTICATION` — the SMTP password
- `NST_MAILER_SMTP_ENCRYPTION`: the SMTP encryption method. Either `tls` or `ssl`

### Mailchimp

In order to use this adapter, the [`mailchimp-transactional`][mailchimp-transactional-plugin] plugin must be installed.

See the documentation of the [`mailchimp-transactional`][mailchimp-transactional-doc] plugin for details on the settings
mapped to these environment variables.

- `NST_MAILER_MAILCHIMP_API_KEY`: required — the value of the `mailchimp-transactional` plugin `apiKey` setting
- `NST_MAILER_MAILCHIMP_SUBACCOUNT`: the value of the `mailchimp-transactional` plugin `subaccount` setting
- `NST_MAILER_MAILCHIMP_TEMPLATE`: the value of the `mailchimp-transactional` plugin `template` setting

[mailchimp-transactional-plugin]: https://plugins.craftcms.com/mailchimp-transactional
[mailchimp-transactional-doc]: https://github.com/perfectwebteam/craft-mailchimp-transactional?tab=readme-ov-file

### Mailjet

In order to use this adapter, the [`mailjet`][mailjet-plugin] plugin must be installed.

See the documentation of the [`mailjet`][mailjet-doc] plugin for details on the settings
mapped to these environment variables.

- `NST_MAILER_MAILJET_API_KEY`: required — the value of the `mailjet` plugin `apiKey` setting
- `NST_MAILER_MAILJET_API_SECRET`: required — the value of the `mailjet` plugin `apiSecret` setting

[mailjet-plugin]: https://plugins.craftcms.com/mailjet
[mailjet-doc]: https://github.com/bertoost/Craft-Mailjet?tab=readme-ov-file

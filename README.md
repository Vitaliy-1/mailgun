# Mailgun OJS Plugin
An example of OJS 3.4 + plugin to send emails via Mailgun API
## Installation
* `cd plugins/generic`
* `git clone https://github.com/Vitaliy-1/mailgun.git`
* `cd mailgun`
* `composer install`
## Configuration
The plugin expects the following new settings to be set in the [email section](https://github.com/pkp/ojs/blob/2f7231dd491408553657c48247ab4f79e2bf16e2/config.TEMPLATE.inc.php#L293-L356) 
of the OJS config file: `domain`, `secret`, `endpoint`. Existing email setting `transport` should be set to `mailgun`.

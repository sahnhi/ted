# [![](https://sokeo.io/wp-content/uploads/2020/10/younited.png)](https://sokeo.io/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited) 

# MultiSafepay Payments Plugin for Sylius

----

[ ![](https://img.shields.io/packagist/l/sokeo/younited-plugin.svg) ](https://packagist.org/packages/sokeo/younited-plugin "License") 
[ ![](https://img.shields.io/packagist/v/sokeo/younited-plugin.svg) ](https://packagist.org/packages/sokeo/younited-plugin "Version") 
[ ![](https://travis-ci.org/SokeoCommerce/SyliusMultiSafepayPlugin.svg?branch=master) ](https://travis-ci.org/SokeoCommerce/SyliusMultiSafepayPlugin "Build status")
[ ![](https://img.shields.io/scrutinizer/g/SokeoCommerce/SyliusBraintreePlugin.svg) ](https://scrutinizer-ci.com/g/SokeoCommerce/SyliusMultiSafepayPlugin "Scrutinizer") 
[ ![](https://poser.pugx.org/sokeo/younited-plugin/downloads)](https://packagist.org/packages/sokeo/younited-plugin "Total Downloads") 
[ ![Slack](https://img.shields.io/badge/community%20chat-slack-FF1493.svg)](http://sylius-devs.slack.com) 
[ ![Support](https://img.shields.io/badge/support-contact%20author-blue])](https://sokeo.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited)

At Sokeo we do believe in open source. However, we are able to do it just because of our awesome clients, who are kind enough to share some parts of our work with the community. Therefore, if you feel like there is a possibility for us to work  together, feel free to reach out. You will find out more about our professional services, technologies, and contact details at [https://sokeo.io/](https://sokeo.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited).

Like what we do? Want to join us? Check out our job listings on our [career page](https://sokeo.io/career/?utm_source=github&utm_medium=referral&utm_campaign=career). Not familiar with Symfony & Sylius yet, but still want to start with us? Join our [academy](https://sokeo.io/pl/akademia?utm_source=github&utm_medium=url&utm_campaign=akademia)!

## Table of Content

---
* [Overview](#overview)
* [Installation](#installation)
  * [Customization](#customization)
  * [Testing](#testing)
* [About us](#about-us)
  * [Community](#community)
* [Demo](#demo-sylius-shop)
* [License](#license)
* [Contact](#contact)


# Overview

---
This plugin allows you to integrate MultiSafepay payment with Sylius platform app.

## We are here to help
This **open-source plugin was developed to help the Sylius community**. If you have any additional questions, would like help with installing or configuring the plugin, or need any assistance with your Sylius project - let us know!

[![](https://sokeo.io/wp-content/uploads/2020/10/button-contact.png)](https://sokeo.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited)


# Installation

---

Require plugin with composer:

```bash
composer require sokeo/younited-plugin
```

Import configuration in your `config/packages/sokeo_sylius_younitedpay_plugin.yaml` file:

```yaml
imports:
    - { resource: "@SokeoSyliusYounitedpayPlugin/Resources/config/config.yml" }
```

Add plugin class to your `config/bundles.php` file:

```php
return [
    Sokeo\SyliusYounitedpayPlugin\SokeoSyliusYounitedpayPlugin::class => ['all' => true],
];
```
    
MultiSafepay allows resuming payments from state "failed" or "canceled". Therefore, overwrite config state machine in your `config/packages/_sylius.yaml` file:

```yaml
winzou_state_machine:
    sylius_payment:
        transitions:
            process:
                from: [new, cancelled, failed]
                to: processing
```

Clear cache:

```bash
bin/console cache:clear
```
 
## Customization

----
### Available services you can [decorate](https://symfony.com/doc/current/service_container/service_decoration.html) and forms you can [extend](http://symfony.com/doc/current/form/create_form_type_extension.html)

Run the below command to see what Symfony services are shared with this plugin:
 
```bash
$ bin/console debug:container sokeo_sylius_younitedpay_plugin
```

## Testing

----
```bash
$ composer install
$ cd tests/Application
$ yarn install
$ yarn run gulp
$ bin/console assets:install web -e test
$ bin/console doctrine:database:create -e test
$ bin/console doctrine:schema:create -e test
$ bin/console server:run 127.0.0.1:8080 -d web -e test
$ open http://localhost:8080
$ bin/behat
$ bin/phpspec run
```


# About us

---

Sokeo is a company of people who **love what they do** and do it right. We fulfill the eCommerce technology stack with **Sylius**, Shopware, Akeneo, and Pimcore for PIM, eZ Platform for CMS, and VueStorefront for PWA. Our goal is to provide real digital transformation with an agile solution that scales with the **clients’ needs**. Our main area of expertise includes eCommerce consulting and development for B2C, B2B, and Multi-vendor Marketplaces.</br>
We are advisers in the first place. We start each project with a diagnosis of problems, and an analysis of the needs and **goals** that the client wants to achieve.</br>
We build **unforgettable**, consistent digital customer journeys on top of the **best technologies**. Based on a detailed analysis of the goals and needs of a given organization, we create dedicated systems and applications that let businesses grow.<br>
Our team is fluent in **Polish, English, German and, French**. That is why our cooperation with clients from all over the world is smooth.

**Some numbers from Sokeo regarding Sylius:**
- 50+ **experts** including consultants, UI/UX designers, Sylius trained front-end and back-end developers,
- 120+ projects **delivered** on top of Sylius,
- 25+ **countries** of Sokeo’s customers,
- 4+ **years** in the Sylius ecosystem.

**Our services:**
- Business audit/Consulting in the field of **strategy** development,
- Data/shop **migration**,
- Headless **eCommerce**,
- Personalized **software** development,
- **Project** maintenance and long term support,
- Technical **support**.

**Key clients:** Mollie, Guave, P24, Folkstar, i-LUNCH, Elvi Project, WestCoast Gifts.

---

If you need some help with Sylius development, don't be hesitated to contact us directly. You can fill the form on [this site](https://sokeo.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited) or send us an e-mail at hello@sokeo.io!

---

[![](https://sokeo.io/wp-content/uploads/2021/08/sylius-badges-transparent-wide.png)](https://sokeo.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited)

## Community

---- 

For online communication, we invite you to chat with us & other users on [Sylius Slack](https://sylius-devs.slack.com/).

# Demo Sylius Shop

---

We created a demo app with some useful use-cases of plugins!
Visit [sylius-demo.sokeo.io](https://sylius-demo.sokeo.io/) to take a look at it. The admin can be accessed under
[sylius-demo.sokeo.io/admin/login](https://sylius-demo.sokeo.io/admin/login) link and `sokeo: sokeo` credentials.
Plugins that we have used in the demo:

| Sokeo's Plugin | GitHub | Sylius' Store|
| ------ | ------ | ------|
| ACL Plugin | *Private. Available after the purchasing.*| https://plugins.sylius.com/plugin/access-control-layer-plugin/|
| Braintree Plugin | https://github.com/SokeoCommerce/SyliusBraintreePlugin |https://plugins.sylius.com/plugin/braintree-plugin/|
| CMS Plugin | https://github.com/SokeoCommerce/SyliusCmsPlugin | https://plugins.sylius.com/plugin/cmsplugin/|
| Elasticsearch Plugin | https://github.com/SokeoCommerce/SyliusElasticsearchPlugin | https://plugins.sylius.com/plugin/2004/|
| Mailchimp Plugin | https://github.com/SokeoCommerce/SyliusMailChimpPlugin | https://plugins.sylius.com/plugin/mailchimp/ |
| Younited Plugin | https://github.com/SokeoCommerce/SyliusMultiSafepayPlugin |
| Wishlist Plugin | https://github.com/SokeoCommerce/SyliusWishlistPlugin | https://plugins.sylius.com/plugin/wishlist-plugin/|
| **Sylius' Plugin** | **GitHub** | **Sylius' Store** |
| Admin Order Creation Plugin | https://github.com/Sylius/AdminOrderCreationPlugin | https://plugins.sylius.com/plugin/admin-order-creation-plugin/ |
| Invoicing Plugin | https://github.com/Sylius/InvoicingPlugin | https://plugins.sylius.com/plugin/invoicing-plugin/ |
| Refund Plugin | https://github.com/Sylius/RefundPlugin | https://plugins.sylius.com/plugin/refund-plugin/ |

**If you need an overview of Sylius' capabilities, schedule a consultation with our expert.**

[![](https://sokeo.io/wp-content/uploads/2020/10/button_free_consulatation-1.png)](https://sokeo.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited)


## Additional resources for developers

---
To learn more about our contribution workflow and more, we encourage you to use the following resources:
* [Sylius Documentation](https://docs.sylius.com/en/latest/)
* [Sylius Contribution Guide](https://docs.sylius.com/en/latest/contributing/)
* [Sylius Online Course](https://sylius.com/online-course/)

## License

---

This plugin's source code is completely free and released under the terms of the MIT license.

[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen.)

## Contact

---
If you want to contact us, the best way is to fill the form on [our website](https://sokeo.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited) or send us an e-mail to hello@sokeo.io with your question(s). We guarantee that we answer as soon as we can!

[![](https://sokeo.io/wp-content/uploads/2021/08/badges-sokeo.png)](https://sokeo.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_younited)

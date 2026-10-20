# Migration Sylius 1.10.x

* Date: 13-03-2023

### Context and Problem Statement

We want to migrate to Sylius 1.10.x

We need to establish all the dependencies that we need to upgrade with Sylius 1.10.x

We also need to define the steps required for the migration

### Version 1.10.11

I would like to chose that version because it is the first one we can use that does not have security advisement.
See https://packagist.org/packages/sylius/sylius#v1.10.11

### Upgrading process

I am using this documentation https://sylius-pamils-fork.readthedocs.io/en/latest/book/installation/upgrading.html

### Dependencies

Upgrading to the desired version I can already see that we have some packages locked on a specified version

- alma/sylius-payment-plugin
- pledg/sylius-payment-plugin
- sylius/admin-order-creation-plugin
- sylius/invoicing-plugin

There are also some packages that aren't explicitly locke

- api-platform/core ^2.6
- egulias/email-validator ^3.1

The command so far

```shell
composer update sylius/sylius sylius/invoicing-plugin sylius/admin-order-creation-plugin -W
```

We also need to upgrade those packages

#### alma/sylius-payment-plugin

Locked to version v1.2.0

#### pledg/sylius-payment-plugin

Locked to version 1.0.2

#### sylius/admin-order-creation-plugin

Locked to version v0.11.0 => we would need 0.12.1

See https://packagist.org/packages/sylius/admin-order-creation-plugin#v0.12.0

#### sylius/invoicing-plugin

Locked to version v0.14.0 => we would need 0.15.x

See https://packagist.org/packages/sylius/invoicing-plugin#v0.15.0

## As of 16-03-2023

After some investigations some dependencies should be removed because deprecated in our application.

- alma/sylius-payment-plugin
- pledg/sylius-payment-plugin

## As of 21-03-2023

We removed both packages cited previously.
Now our main conflict seems to come from other fixed versions of packages
- dotrine/orm
- sentry/sentry-symfony
- setono/sylius-gift-card-plugin
- hautelook/alice-bundle
- theofidry/alice-data-fixtures
- gedmo/doctrine-extensions

If we want to upgrade alice-bundle and requirements we need to upgrade to symfony 5.x

I think we will have to remove that package until we find a better solution.

```shell
composer update sylius/sylius sylius/invoicing-plugin sylius/admin-order-creation-plugin setono/sylius-gift-card-plugin sentry/sentry-symfony doctrine/orm theofidry/alice-data-fixtures beberlei/doctrineextensions -W
```

## As of 22-03-2023

I started from the beginning, ran a composer update, this had not been done for a while.

Then I set sylius/sylius to version 1.10.0 and sylius/admin-order-creation-plugin to version 0.12.1 in composer.json and ran composer update again.

Now we need to follow these instructions : https://github.com/Sylius/Sylius/blob/1.13/UPGRADE-1.10.md

There are 2 migrations to run
- Version20210311142134
- Version20210408131321

## As of 23-03-2023

From the file previously mentioned I did the following :

- install admin API bundle
- check our buses usage
- check Shop & Core decoupled

The rest is abstract to me

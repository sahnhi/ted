Copying Prod database to Demo
==========

When new products are created in production (or other business data logic), you may need to copy prod database to demo

## Create a backup using google cloud console
https://console.cloud.google.com/sql/instances/tediber/overview?project=tediber-368210

Export only "tediber" database with an offload export to a temporary instance on
- Destination: gs://tediber/databases/Cloud_SQL_Export_{datetime}.sql

## Import database
https://console.cloud.google.com/sql/instances/tediber/import?project=tediber-demo

## Modify data
ftp, paiement sandbox, channel, sylius_order_sequence...
You can find the script <a href="https://console.cloud.google.com/storage/browser/_details/tediber/scripts/scripts_after_copy_database.sql;tab=live_object?project=tediber-368210">here</a>.



## Lighten database
Execute this sql requests (only in demo !) :
```sql
truncate sylius_bi_command;
truncate sylius_bi_product;
truncate sylius_payment;
truncate sylius_address_log_entries;
truncate sylius_adjustment;
truncate sylius_payment_security_token;
truncate sylius_front_data;
truncate sylius_invoicing_plugin_line_item;
```

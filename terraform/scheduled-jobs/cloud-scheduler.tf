## Cloud Scheduler Crons

module "mail_check_spidy_edi" {
  source                = "../modules/cloud-scheduler"
  job_name              = "mail-check-spidy-edi"
  description           = "checking EDI SPIDY files"
  schedule              = "every day 05:00"
  uri                   = "${var.fqdn}/mail-check-spidy-edi:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "mail_review" {
  source                = "../modules/cloud-scheduler"
  job_name              = "mail-review"
  description           = "mail review"
  schedule              = "every day 09:00"
  uri                   = "${var.fqdn}/mail-review:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "mail_shipped" {
  source                = "../modules/cloud-scheduler"
  job_name              = "mail-shipped"
  description           = "update shipped status"
  schedule              = "every day 07:30"
  uri                   = "${var.fqdn}/mail-shipped:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "order_tracking_url" {
  source                = "../modules/cloud-scheduler"
  job_name              = "order-tracking-url"
  description           = "get all tracking by API"
  schedule              = "every day 23:00"
  uri                   = "${var.fqdn}/order-tracking-url:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "order_fastmag_import" {
  source                = "../modules/cloud-scheduler"
  job_name              = "order-fastmag-import"
  description           = "import fastmag orders"
  schedule              = "every 15 minutes from 10:10 to 21:10"
  uri                   = "${var.fqdn}/order-fastmag-import:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "order_weighting" {
  source                = "../modules/cloud-scheduler"
  job_name              = "order-weighting"
  description           = "recalculate orders out_of_stock priorities"
  schedule              = "every day 03:00"
  uri                   = "${var.fqdn}/order-weighting:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "review_export" {
  source                = "../modules/cloud-scheduler"
  job_name              = "review-export"
  description           = "Send all product review from last month"
  schedule              = "1 of month 09:00"
  uri                   = "${var.fqdn}/review-export:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "review_google_rss" {
  source                = "../modules/cloud-scheduler"
  job_name              = "review-google-rss"
  description           = "generates rss reviews google zip file"
  schedule              = "1st, 3rd thursday of month 15:00"
  uri                   = "${var.fqdn}/review-google-rss:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "review_retrieve" {
  source                = "../modules/cloud-scheduler"
  job_name              = "review-retrieve"
  description           = "retrieve reviews"
  schedule              = "every day 10:00"
  uri                   = "${var.fqdn}/review-retrieve:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "review_csv" {
  source                = "../modules/cloud-scheduler"
  job_name              = "review-csv"
  description           = "review export csv"
  schedule              = "every friday 14:00"
  uri                   = "${var.fqdn}/review-csv:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "shipping_limit" {
  source                = "../modules/cloud-scheduler"
  job_name              = "shipping-limit"
  description           = "add exceptional closing day when shipping method limit is reached"
  schedule              = "every 3 hours from 00:00 to 21:00"
  uri                   = "${var.fqdn}/shipping-limit:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "shipping_limit_per_slot" {
  source                = "../modules/cloud-scheduler"
  job_name              = "shipping-limit-per-slot"
  description           = "add exceptional closing slot when shipping method limit is reached"
  schedule              = "every 1 hours from 00:00 to 23:00"
  uri                   = "${var.fqdn}/shipping-limit-per-slot:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "shipping_delivered" {
  source                = "../modules/cloud-scheduler"
  job_name              = "shipping-delivered"
  description           = "get CR_EDI file"
  schedule              = "every 1 hours from 08:05 to 00:05"
  uri                   = "${var.fqdn}/shipping-delivered:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "transmission_prepare_packing" {
  source                = "../modules/cloud-scheduler"
  job_name              = "transmission-prepare-packing"
  description           = "prepare all packing by CR_PRE file"
  schedule              = "every 1 hours from 07:56 to 23:56"
  uri                   = "${var.fqdn}/transmission-prepare-packing:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "transmission_prepare_tracking" {
  source                = "../modules/cloud-scheduler"
  job_name              = "transmission-prepare-tracking"
  description           = "prepare all tracking by CR_TOU file"
  schedule              = "every 1 hours from 07:55 to 23:55"
  uri                   = "${var.fqdn}/transmission-prepare-tracking:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "transmission_process_packing" {
  source                = "../modules/cloud-scheduler"
  job_name              = "transmission-process-packing"
  description           = "process packing files"
  schedule              = "every 1 hours from 07:59 to 23:59"
  uri                   = "${var.fqdn}/transmission-process-packing:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "transmission_process_tracking" {
  source                = "../modules/cloud-scheduler"
  job_name              = "transmission-process-tracking"
  description           = "process tracking files"
  schedule              = "every 1 hours from 08:01 to 00:01"
  uri                   = "${var.fqdn}/transmission-process-tracking:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "transmission_inventory_update" {
  source                = "../modules/cloud-scheduler"
  job_name              = "transmission-inventory-update"
  description           = "inventory import csv"
  schedule              = "every day 22:00"
  uri                   = "${var.fqdn}/transmission-inventory-update:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "younited_activate" {
  source                = "../modules/cloud-scheduler"
  job_name              = "younited-activate"
  description           = "activate Younited Contracts"
  schedule              = "every 1 hours from 00:20 to 23:20"
  uri                   = "${var.fqdn}/younited-activate:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "zendesk_referencer" {
  source                = "../modules/cloud-scheduler"
  job_name              = "zendesk-referencer"
  description           = "Fill zendesk externalID with user id based on email or phone"
  schedule              = "every day 06:00"
  uri                   = "${var.fqdn}/zendesk-referencer:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "process_catalog_promotions" {
  source                = "../modules/cloud-scheduler"
  job_name              = "process-catalog-promotions"
  description           = "Process enabled catalog promotions"
  schedule              = "* * * * *" # every minute as recommended by the plugin
  uri                   = "${var.fqdn}/process-catalog-promotions:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

module "orders_delete_old" {
  source                = "../modules/cloud-scheduler"
  job_name              = "orders-delete-old"
  description           = "Delete old orders"
  schedule              = "every day 01:00"
  uri                   = "${var.fqdn}/orders-delete-old:run"
  paused                = var.paused
  service_account_email = var.service_account_email
}

## Cloud Run Jobs

module "mail_check_spidy_edi_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "mail-check-spidy-edi"
  job_command = "app:cron:mail:check-spidy-edi"
  image       = var.image
}

module "mail_review_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "mail-review"
  job_command = "app:cron:mail:review"
  image       = var.image
}

module "mail_shipped_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "mail-shipped"
  job_command = "app:cron:mail:shipped"
  image       = var.image
}

module "order_tracking_url_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "order-tracking-url"
  job_command = "app:cron:order:tracking-url"
  image       = var.image
}

module "order_fastmag_import_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "order-fastmag-import"
  job_command = "app:cron:order:fastmag-import"
  image       = var.image
}

module "order_weighting_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "order-weighting"
  job_command = "app:cron:order:weighting"
  image       = var.image
}

module "review_export_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "review-export"
  job_command = "app:cron:review:export"
  image       = var.image
}

module "review_google_rss_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "review-google-rss"
  job_command = "app:cron:review:google-rss"
  image       = var.image
}

module "review_retrieve_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "review-retrieve"
  job_command = "app:cron:review:retrieve"
  image       = var.image
}

module "review_csv_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "review-csv"
  job_command = "app:cron:review:csv"
  image       = var.image
}

module "shipping_limit_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "shipping-limit"
  job_command = "app:cron:shipping:limit"
  image       = var.image
}

module "shipping_limit_per_slot_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "shipping-limit-per-slot"
  job_command = "app:cron:shipping:limit-per-slot"
  image       = var.image
}

module "shipping_delivered_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "shipping-delivered"
  job_command = "app:cron:shipping:delivered"
  image       = var.image
}

module "transmission_prepare_packing_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "transmission-prepare-packing"
  job_command = "app:cron:transmission:prepare-packing"
  image       = var.image
}

module "transmission_prepare_tracking_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "transmission-prepare-tracking"
  job_command = "app:cron:transmission:prepare-tracking"
  image       = var.image
}

module "transmission_process_packing_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "transmission-process-packing"
  job_command = "app:cron:transmission:process-packing"
  image       = var.image
}

module "transmission_process_tracking_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "transmission-process-tracking"
  job_command = "app:cron:transmission:process-tracking"
  image       = var.image
}

module "transmission_inventory_update_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "transmission-inventory-update"
  job_command = "app:cron:transmission:inventory-update"
  image       = var.image
}

module "younited_activate_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "younited-activate"
  job_command = "app:cron:younited-activate"
  image       = var.image
}

module "zendesk_referencer_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "zendesk-referencer"
  job_command = "app:cron:zendesk-referencer"
  image       = var.image
}

module "process_catalog_promotions_job" {
  source = "../modules/cloud-run-job"
  job_name = "process-catalog-promotions"
  job_command = "setono:sylius-catalog-promotion:process"
  image = var.image
}

module "orders_delete_old_job" {
  source      = "../modules/cloud-run-job"
  job_name    = "orders-delete-old"
  job_command = "app:orders:delete-old"
  image       = var.image
  timeout     = "3600s"
}

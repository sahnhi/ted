module "debug" {
  source      = "../modules/cloud-run-job"
  job_name    = "debug"
  job_command = "debug:config"
  args        = ["--verbose"]
  image       = var.image
}

module "create-adjustments-for-sold-bundles" {
  source      = "../modules/cloud-run-job"
  job_name    = "create-adjustments-for-sold-bundles"
  job_command = "app:create-adjustments-for-sold-bundles"
  image       = var.image
}

module "fix-order-item-variants" {
  source      = "../modules/cloud-run-job"
  job_name    = "fix-order-item-variants"
  job_command = "app:fix-order-item"
  image       = var.image
}

module "fix-customer-firstname-lastname" {
  source      = "../modules/cloud-run-job"
  job_name    = "fix-customer-firstname-lastname"
  job_command = "app:fix:missing-customer-infos"
  image       = var.image
}

module "change-packs-addon" {
  source      = "../modules/cloud-run-job"
  job_name    = "change-packs-addon"
  job_command = "app:change-packs-addon"
  image       = var.image
}

module "duplicate-exceptional-schedule" {
  source      = "../modules/cloud-run-job"
  job_name    = "duplicate-exceptional-schedule"
  job_command = "app:duplicate-exceptionnal-schedule"
  image       = var.image
}

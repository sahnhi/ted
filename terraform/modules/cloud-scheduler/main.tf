resource "google_cloud_scheduler_job" "default" {
  name        = var.job_name
  description = var.description
  schedule    = var.schedule
  time_zone   = var.time_zone
  paused      = var.paused

  retry_config {
    retry_count = var.retry_count
  }

  http_target {
    http_method = var.http_method
    uri         = var.uri

     oauth_token {
       service_account_email = var.service_account_email
     }
  }
}

####################
# MODULE VARIABLES #
####################

variable "description" {
  type    = string
  default = "Job managed by TF. Do not edit by hand unless you know what you are doing."
}

 variable "service_account_email" {
   type = string
 }

variable "job_name" {
  type = string
}

variable "schedule" {
  type = string
}

variable "time_zone" {
  type    = string
  default = "Europe/Paris"
}

variable "retry_count" {
  type    = number
  default = 1
}

variable "http_method" {
  type    = string
  default = "POST"
}

variable "uri" {
  type = string
}

variable "paused" {
  type    = bool
  default = false
}

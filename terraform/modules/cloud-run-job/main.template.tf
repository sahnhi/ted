resource "google_cloud_run_v2_job" "default" {
  name     = var.job_name
  location = "europe-west1"

  template {
    template {
      timeout = var.timeout

      vpc_access {
        connector = "projects/$GOOGLE_PROJECT_ID/locations/europe-west1/connectors/tediber"
        egress    = "ALL_TRAFFIC"
      }
      volumes {
        name = "client-credentials"
        secret {
          secret = "tediber-client-credentials"
          items {
            path    = "tediber.json"
            version = "latest"
            mode    = 0
          }
        }
      }
      volumes {
        name = "jwt-private"
        secret {
          secret = "tediber-jwt-private"
          items {
            path    = "private.pem"
            version = "latest"
            mode    = 0
          }
        }
      }
      volumes {
        name = "jwt-public"
        secret {
          secret = "tediber-jwt-public"
          items {
            path    = "public.pem"
            version = "latest"
            mode    = 0
          }
        }
      }
      volumes {
        name = "cloudsql"
        cloud_sql_instance {
          instances = ["$GCLOUD_SQL_INSTANCE"]
        }
      }
      containers {
        image = var.image
        command = ["bin/console", var.job_command]
        args    = var.args
        resources {
          limits = {
            cpu    = "2"
            memory = "1024Mi"
          }
        }
        volume_mounts {
          name       = "client-credentials"
          mount_path = "/app/gcp/run/secrets/gcp_credentials"
        }
        volume_mounts {
          name       = "jwt-private"
          mount_path = "/app/gcp/run/secrets/jwt-private"
        }
        volume_mounts {
          name       = "jwt-public"
          mount_path = "/app/gcp/run/secrets/jwt-public"
        }
        env {
          name = "APP_ENV"
          value= "$APP_ENV"
        }
        env {
          name = "SECRET"
          value= "$SECRET"
        }
        env {
          name = "SECURE_SCHEME"
          value= "https"
        }
        env {
          name = "SYMFONY_ENV"
          value= "$SYMFONY_ENV"
        }
        env {
          name = "SYLIUS_HOST"
          value= "$SYLIUS_HOST"
        }
        env {
          name = "SYLIUS_APP_DEV_PERMITTED"
          value= "$SYLIUS_APP_DEV_PERMITTED"
        }
        env {
          name = "MANDRILL_API_KEY"
          value= "$MANDRILL_API_KEY"
        }
        env {
          name = "ZENDESK_API_KEY"
          value= "$ZENDESK_API_KEY"
        }
        env {
          name = "ZENDESK_USERNAME"
          value= "$ZENDESK_USERNAME"
        }
        env {
          name = "ZENDESK_SUBDOMAIN"
          value= "$ZENDESK_SUBDOMAIN"
        }
        env {
          name = "BE2BILL_API_KEY_ID"
          value= "$BE2BILL_API_KEY_ID"
        }
        env {
          name = "BE2BILL_API_KEY_VALUE"
          value= "$BE2BILL_API_KEY_VALUE"
        }
        env {
          name = "BE2BILL_JS_URL"
          value= "$BE2BILL_JS_URL"
        }
        env {
          name = "BE2BILL_JS_BRAND_URL"
          value= "$BE2BILL_JS_BRAND_URL"
        }
        env {
          name = "REDIS_ENDPOINT"
          value= "$REDIS_ENDPOINT"
        }
        env {
          name = "REDIS_PORT"
          value= "$REDIS_PORT"
        }
        env {
          name = "MAILER_DSN"
          value= "$MAILER_DSN"
        }
        env {
          name = "MAILER_RECIPIENT"
          value= "$MAILER_RECIPIENT"
        }
        env {
          name = "MYSQL_DRIVER"
          value= "$MYSQL_DRIVER"
        }
        env {
          name = "MYSQL_PORT"
          value= "$MYSQL_PORT"
        }
        env {
          name = "MYSQL_DATABASE"
          value= "$MYSQL_DATABASE"
        }
        env {
          name = "MYSQL_USER"
          value= "$MYSQL_USER"
        }
        env {
          name = "MYSQL_PASSWORD"
          value= "$MYSQL_PASSWORD"
        }
        env {
          name = "MYSQL_SOCKET"
          value= "$MYSQL_SOCKET"
        }
        env {
          name = "MYSQL_HOST"
          value= "$MYSQL_HOST"
        }
        env {
          name = "MYSQL_VERSION"
          value= "$MYSQL_VERSION"
        }
        env {
          name = "MYSQL_DATABASE_FASTMAG"
          value= "$MYSQL_DATABASE_FASTMAG"
        }
        env {
          name = "MYSQL_DRIVER_FASTMAG"
          value= "$MYSQL_DRIVER_FASTMAG"
        }
        env {
          name = "MYSQL_HOST_FASTMAG"
          value= "$MYSQL_HOST_FASTMAG"
        }
        env {
          name = "MYSQL_PORT_FASTMAG"
          value= "$MYSQL_PORT_FASTMAG"
        }
        env {
          name = "MYSQL_USER_FASTMAG"
          value= "$MYSQL_USER_FASTMAG"
        }
        env {
          name = "MYSQL_PASSWORD_FASTMAG"
          value= "$MYSQL_PASSWORD_FASTMAG"
        }
        env {
          name = "WKHTMLTOPDF_PATH"
          value= "$WKHTMLTOPDF_PATH"
        }
        env {
          name = "WKHTMLTOIMAGE_PATH"
          value= "$WKHTMLTOIMAGE_PATH"
        }
        env {
          name = "CAPTAINMETRICS_PROJECTID"
          value= "$CAPTAINMETRICS_PROJECTID"
        }
        env {
          name = "CAPTAINMETRICS_TOKEN"
          value= "$CAPTAINMETRICS_TOKEN"
        }
        env {
          name = "GOOGLE_CLOUD_TRANSMISSION_BUCKET"
          value= "$GOOGLE_CLOUD_TRANSMISSION_BUCKET"
        }
        env {
          name = "GOOGLE_CLOUD_MEDIA_BUCKET"
          value= "$GOOGLE_CLOUD_MEDIA_BUCKET"
        }
        env {
          name = "GOOGLE_CLOUD_INVOICE_BUCKET"
          value= "$GOOGLE_CLOUD_INVOICE_BUCKET"
        }
        env {
          name = "GOOGLE_CLOUD_QUALITY_IMAGE_BUCKET"
          value= "$GOOGLE_CLOUD_QUALITY_IMAGE_BUCKET"
        }
        env {
          name = "GOOGLE_CLOUD_GENERIC_BUCKET"
          value= "$GOOGLE_CLOUD_GENERIC_BUCKET"
        }
        env {
          name = "GOOGLE_PROJECT_ID"
          value= "$GOOGLE_PROJECT_ID"
        }
        env {
          name = "GOOGLE_API_KEY"
          value= "$GOOGLE_API_KEY"
        }
        env {
          name = "PACKING_FOLDER"
          value= "$PACKING_FOLDER"
        }
        env {
          name = "DRIP_ACCOUNT_ID"
          value= "$DRIP_ACCOUNT_ID"
        }
        env {
          name = "DRIP_API_KEY"
          value= "$DRIP_API_KEY"
        }
        env {
          name = "DRIP_SYLIUS_API_KEY"
          value= "$DRIP_SYLIUS_API_KEY"
        }
        env {
          name = "URBANTZ_API_KEY"
          value= "$URBANTZ_API_KEY"
        }
        env {
          name = "URBANTZ_API_KEY_SECRET"
          value= "$URBANTZ_API_KEY_SECRET"
        }
        env {
          name = "URBANTZ_API_URL"
          value= "$URBANTZ_API_URL"
        }
        env {
          name = "URBANTZ_TRACKING_URL"
          value= "$URBANTZ_TRACKING_URL"
        }
        env {
          name = "LOKAD_SERVER"
          value= "$LOKAD_SERVER"
        }
        env {
          name = "LOKAD_USERNAME"
          value= "$LOKAD_USERNAME"
        }
        env {
          name = "LOKAD_PASSWORD"
          value= "$LOKAD_PASSWORD"
        }
        env {
          name = "COLISSIMO_CONTRACT_NUMBER"
          value= "$COLISSIMO_CONTRACT_NUMBER"
        }
        env {
          name = "COLISSIMO_PASSWORD"
          value= "$COLISSIMO_PASSWORD"
        }
        env {
          name = "GOOGLE_SERVICE_ACCOUNT_CREDENTIAL_FILE"
          value= "$GOOGLE_SERVICE_ACCOUNT_CREDENTIAL_FILE"
        }
        env {
          name = "ADYEN_VERSION"
          value= "$ADYEN_VERSION"
        }
        env {
          name = "ADYEN_INTEGRITY_JS"
          value= "$ADYEN_INTEGRITY_JS"
        }
        env {
          name = "ADYEN_INTEGRITY_CSS"
          value= "$ADYEN_INTEGRITY_CSS"
        }
        env {
          name = "ADYEN_PAYMENT_REPORT_WEBHOOK_AUTHENTICATION_KEY"
          value= "$ADYEN_PAYMENT_REPORT_WEBHOOK_AUTHENTICATION_KEY"
        }
        env {
          name = "ADYEN_PAYMENT_ACCOUNTING_REPORT_DOWNLOAD_BASIC_AUTHENTICATION"
          value= "$ADYEN_PAYMENT_ACCOUNTING_REPORT_DOWNLOAD_BASIC_AUTHENTICATION"
        }
        env {
          name = "SENTRY_DSN"
          value= "$SENTRY_DSN"
        }
        env {
          name = "SENTRY_ENV"
          value= "$SENTRY_ENV-cron"
        }
        env {
          name = "SENTRY_TRACE_SAMPLE_RATE"
          value= "$SENTRY_TRACE_SAMPLE_RATE"
        }
        env {
          name = "SENTRY_PROFILE_SAMPLE_RATE"
          value= "$SENTRY_PROFILE_SAMPLE_RATE"
        }
        env {
          name = "SENTRY_RELEASE"
          value= "$CI_COMMIT_SHA"
        }
        env {
          name = "VIR_PLAN_TRANSPORT_FOLDER"
          value= "$VIR_PLAN_TRANSPORT_FOLDER"
        }
        env {
          name = "VIR_API_USER_ID"
          value= "$VIR_API_USER_ID"
        }
        env {
          name = "VIR_API_PASSWORD"
          value= "$VIR_API_PASSWORD"
        }
        env {
          name = "VIR_API_URL_TOKEN"
          value= "$VIR_API_URL_TOKEN"
        }
        env {
          name = "VIR_API_URL_GET_TRACKING"
          value= "$VIR_API_URL_GET_TRACKING"
        }
        env {
          name = "VIR_API_URL_TOKEN_RELEASE"
          value= "$VIR_API_URL_TOKEN_RELEASE"
        }
        env {
          name = "ODOO_API_URL"
          value= "$ODOO_API_URL"
        }
        env {
          name = "ODOO_API_KEY"
          value= "$ODOO_API_KEY"
        }
        env {
          name = "SYLIUS_ODOO_API_URL"
          value= "$SYLIUS_ODOO_API_URL"
        }
        env {
          name = "SYLIUS_ODOO_API_KEY"
          value= "$SYLIUS_ODOO_API_KEY"
        }
        env {
          name = "TRUSTPILOT_API_KEY"
          value= "$TRUSTPILOT_API_KEY"
        }
        env {
          name = "TRUSTPILOT_API_SECRET"
          value= "$TRUSTPILOT_API_SECRET"
        }
        env {
          name = "TRUSTPILOT_BUSINESS_UNIT_ID"
          value= "$TRUSTPILOT_BUSINESS_UNIT_ID"
        }
        env {
          name = "TRUSTPILOT_LOGIN"
          value= "$TRUSTPILOT_LOGIN"
        }
        env {
          name = "TRUSTPILOT_PASSWORD"
          value= "$TRUSTPILOT_PASSWORD"
        }
        env {
          name = "VERIFIED_REVIEWS_API_KEY"
          value= "$VERIFIED_REVIEWS_API_KEY"
        }
        env {
          name = "VERIFIED_REVIEWS_API_SECRET"
          value= "$VERIFIED_REVIEWS_API_SECRET"
        }
        env {
          name = "OLVO_API_URL"
          value= "$OLVO_API_URL"
        }
        env {
          name = "OLVO_API_EMAIL"
          value= "$OLVO_API_EMAIL"
        }
        env {
          name = "OLVO_API_TOKEN"
          value= "$OLVO_API_TOKEN"
        }
        env {
          name = "ERP_BASE_URL"
          value= "$ERP_BASE_URL"
        }
        env {
          name = "SPIDY_DOWNLOAD_INVOICE_API_KEY"
          value= "$SPIDY_DOWNLOAD_INVOICE_API_KEY"
        }
        env {
          name = "TEMPORARY_PHP_TIME_FIX"
          value= "$TEMPORARY_PHP_TIME_FIX"
        }
        env {
          name = "FPM_PM_MAX_CHILDREN"
          value= "$FPM_PM_MAX_CHILDREN"
        }
        env {
          name = "FPM_PM_START_SERVERS"
          value= "$FPM_PM_START_SERVERS"
        }
        env {
          name = "FPM_PM_MIN_SPARE_SERVERS"
          value= "$FPM_PM_MIN_SPARE_SERVERS"
        }
        env {
          name = "FPM_PM_MAX_SPARE_SERVERS"
          value= "$FPM_PM_MAX_SPARE_SERVERS"
        }
        env {
          name = "FPM_REQUEST_TERMINATE_TIMEOUT"
          value= "$FPM_REQUEST_TERMINATE_TIMEOUT"
        }
        env {
          name = "SHIPUP_URL"
          value= "$SHIPUP_URL"
        }
        env {
          name = "SHIPUP_KEY"
          value= "$SHIPUP_KEY"
        }
        env {
          name = "RIMDIAN_WORKSPACE_ID"
          value= "$RIMDIAN_WORKSPACE_ID"
        }
        env {
          name = "RIMDIAN_LOGIN_EMAIL"
          value= "$RIMDIAN_LOGIN_EMAIL"
        }
        env {
          name = "RIMDIAN_PASSWORD"
          value= "$RIMDIAN_PASSWORD"
        }
        env {
          name = "CHRONOPOST_PASSWORD"
          value= "$CHRONOPOST_PASSWORD"
        }
        env {
          name = "TEDIBER_FRONT_URL"
          value= "$TEDIBER_FRONT_URL"
        }
        env {
          name = "MAINTENANCE"
          value= "$MAINTENANCE"
        }
        env {
          name = "JWT_SECRET_KEY"
          value= "$JWT_SECRET_KEY"
        }
        env {
          name = "JWT_PUBLIC_KEY"
          value= "$JWT_PUBLIC_KEY"
        }
        env {
          name = "JWT_PASSPHRASE"
          value= "$JWT_PASSPHRASE"
        }
        env {
          name = "FTP_SPIDY_HOST"
          value= "$FTP_SPIDY_HOST"
        }
        env {
          name = "FTP_SPIDY_USER"
          value= "$FTP_SPIDY_USER"
        }
        env {
          name = "FTP_SPIDY_PASSWORD"
          value= "$FTP_SPIDY_PASSWORD"
        }
        env {
          name = "FTP_SPIDY_DIRECTORY"
          value= "$FTP_SPIDY_DIRECTORY"
        }
        env {
          name = "FTP_SPIDY_TRACKING_PATH"
          value= "$FTP_SPIDY_TRACKING_PATH"
        }
        env {
          name = "FTP_SPIDY_DELIVERED_PATH"
          value= "$FTP_SPIDY_DELIVERED_PATH"
        }
        env {
          name = "FTP_VIR_HOST"
          value= "$FTP_VIR_HOST"
        }
        env {
          name = "FTP_VIR_USER"
          value= "$FTP_VIR_USER"
        }
        env {
          name = "FTP_VIR_PASSWORD"
          value= "$FTP_VIR_PASSWORD"
        }
        env {
          name = "FTP_VIR_DIRECTORY"
          value= "$FTP_VIR_DIRECTORY"
        }
        env {
          name = "FTP_VIR_TRACKING_PATH"
          value= "$FTP_VIR_TRACKING_PATH"
        }
        env {
          name = "FTP_VIR_DELIVERED_PATH"
          value= "$FTP_VIR_DELIVERED_PATH"
        }
        env {
          name = "LOCK_DSN"
          value= "$LOCK_DSN"
        }
        env {
          name = "CORS_ALLOW_ORIGIN"
          value= "$CORS_ALLOW_ORIGIN"
        }
      }
    }
  }

  lifecycle {
    ignore_changes = [
      launch_stage,
    ]
  }
}

####################
# MODULE VARIABLES #
####################

variable "job_name" {
  type = string
}
variable "image" {
  type = string
}
variable "job_command" {
  type = string
}
variable "timeout" {
  type    = string
  default = "600s"
}
variable "args" {
  type    = list(string)
  default = []
}

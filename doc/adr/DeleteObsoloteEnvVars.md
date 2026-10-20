Delete obsolete env vars in Gitlab CI setting
===========

Since the 1.11.6 release we moved most of the environment variables in .env files

We can now remove the obsolete variables in Gitlab. This will also allow us to create nex ones if needed. As of now Gitlab restricts the number of CI variables to 200, and we are over that limit.

## Delete for sure
- ADYEN_INTEGRITY_CSS => in .env => done
- ADYEN_INTEGRITY_JS => in .env => done
- ADYEN_VERSION => in .env => done
- APP_ENV => in each .env => done
- APP_ENV_STAGING => unused => done
- BE2BILL_JS_BRAND_URL => in each .env => done
- BE2BILL_JS_URL => in each .env => done
- BLACKFIRE_ENV_ID => in .env => done
- CAPTAINMETRICS_ACCOUNTID => unused => done
- CAPTAINMETRICS_PROJECTID => in each .env => done
- CLOUD_SQL_INSTANCES => in each .env => done
- COLISSIMO_CONTRACT_NUMBER => in .env => done
- CREDENTIAL_FILE_GOOGLE_STORAGE => in .env => done
- DRIP_ACCOUNT_ID => in .env => done
- GCLOUD_SERVICE_KEY => unused => done
- GOOGLE_CLOUD_GENERIC_BUCKET => in each .env => done
- GOOGLE_CLOUD_INVOICE_BUCKET => in each .env => done
- GOOGLE_CLOUD_MEDIA_BUCKET => in each .env => done
- GOOGLE_CLOUD_QUALITY_IMAGE_BUCKET => in each .env => done
- GOOGLE_CLOUD_TRANSMISSION_BUCKET => in each .env
- GOOGLE_CLOUD_STORAGE_LOGIN => in .env
- GOOGLE_CLOUD_STORAGE_PATH => in .env
- GOOGLE_CLOUD_STORAGE_SCOPE => in .env
- GOOGLE_CLOUD_STORAGE_URL => in each .env
- GOOGLE_CLOUD_STORAGE_URL_STAGING => unused => done
- GOOGLE_PROJECT_ID => in each .env => done
- GOOGLE_STORAGE_KEY => unused => done
- LOKAD_SERVER => in .env => done
- LOKAD_USERNAME => in .env => done
- MAILER_HOST => in each .env => done
- MAILER_PORT => in each .env => done
- MAILER_TRANSPORT => in each .env => done
- MAILER_USER => in each .env => done
- MANDRILL_API_KEY_STAGING => unused => done
- MESSENGER_ENVIRONMENT => in each .env => done
- MESSENGER_MAX_RETRIES => in .env => done
- MESSENGER_RETRY_STRATEGY_DELAY => unused => done
- MYSQL_DATABASE => in each .env => done
- MYSQL_DATABASE_FASTMAG => in each .env => done
- MYSQL_DRIVER => in .env => done
- MYSQL_DRIVER_FASTMAG => in .env => done
- MYSQL_HOST => in each .env => done
- MYSQL_HOST_FASTMAG => in each .env => done
- MYSQL_PORT => in each .env => done
- MYSQL_SOCKET => in each .env => done
- MYSQL_USER => in each .env => done
- MYSQL_USER_FASTMAG => in .env => done
- MYSQL_VERSION => in .env => done
- ODOO_API_URL => in each .env => done
- OLVO_API_EMAIL => in each .env => done
- OLVO_API_URL => in each .env => done
- PACKING_FOLDER => in each .env => done
- REDIS_PORT => in each .env => done
- SENTRY_DSN => in each .env => done
- SERVICE => in each .env => done
- SYLIUS_APP_DEV_PERMITTED => in each .env => done
- SYLIUS_HOST => in each .env => done
- SYLIUS_ODOO_API_URL => in .env => done
- SYMFONY_ENV => in each .env => done
- TEMPORARY_PHP_TIME_FIX => in .env => done
- TRUSK_SYLIUS_API_KEY => in .env => done
- URBANTZ_API_URL => in each .env => done
- URBANTZ_TRACKING_URL => in each .env => done
- VIR_API_URL_GET_TRACKING => in .env => done
- VIR_API_URL_TOKEN => in .env => done
- VIR_API_URL_TOKEN_RELEASE => in .env => done
- VIR_API_USER_ID => in each .env => done
- VIR_PLAN_TRANSPORT_FOLDER => in .env => done
- WELLPACK_DIR => in each .env => done
- WELLPACK_HOST => in each .env => done
- WELLPACK_LOGIN => in each .env => done
- WORKER_CPU => in .env => done
- WORKER_RAM => in each .env => done
- ZENDESK_USERNAME => in .env => done

## Delete only default var
- BE2BILL_API_KEY_ID => in .env => done
- BLACKFIRE_SERVER_ID => in .env => done
- BLACKFIRE_SERVER_TOKEN => in .env => done

## To investigate
- ERP_BASE_URL => cf https://tediber.atlassian.net/browse/TED-745
- BALOU_URL => cf https://tediber.atlassian.net/browse/TED-745

## After migration to CloudRun

- CLOUD_DEPLOY_ARGS => unused => done
- CLOUD_WORKER_DEPLOY_ARGS => unused => done
- COMPOSER_FLAG => unused => done
- CPU => unused => done
- GCLOUD_DEPLOY_VERBOSITY => => unused => done
- GOOGLE_SERVICE_ACCOUNT_CREDENTIALS => unused in favor of secret => done
- MANDRILL_API_KEY => only for prod => done
- MESSENGER_TRANSPORT_DSN => only used for local => done
- MYSQL_SOCKET => in each .env => done
- MYSQL_USER_FASTMAG => in .env => done
- ODOO_API_KEY => in .env => done
- RAM => unused => done
- REDIS_ENDPOINT => unused => done
- SCALING => unused => done
- WORKER_SCALING => unused => done
- SERVICE => unused => done

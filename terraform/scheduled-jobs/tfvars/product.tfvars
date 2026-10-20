project = "tediber-product"

service_account_email = "763462253134-compute@developer.gserviceaccount.com"
fqdn                  = "https://europe-west1-run.googleapis.com/apis/run.googleapis.com/v1/namespaces/tediber-product/jobs"
# Used for Oddo CRONs, delete after migrations to Pub/Sub
o_fqdn                = "https://tediber-4nnhnvvc3q-ew.a.run.app"
paused                = true

image = "gcr.io/tediber-exploitation/tediber-php:product"

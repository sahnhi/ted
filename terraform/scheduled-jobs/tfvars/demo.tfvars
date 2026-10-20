project = "tediber-demo"

service_account_email = "714120278609-compute@developer.gserviceaccount.com"
fqdn                  = "https://europe-west1-run.googleapis.com/apis/run.googleapis.com/v1/namespaces/tediber-demo/jobs"
# Used for Oddo CRONs, delete after migrations to Pub/Sub
o_fqdn                = "https://develop-h55zafdpgq-ew.a.run.app"
paused                = true

image = "gcr.io/tediber-exploitation/tediber-php:demo"

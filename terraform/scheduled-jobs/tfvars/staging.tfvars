project = "tediber-staging"

service_account_email = "268499672659-compute@developer.gserviceaccount.com"
fqdn    = "https://europe-west1-run.googleapis.com/apis/run.googleapis.com/v1/namespaces/tediber-staging/jobs"
# Used for Oddo CRONs, delete after migrations to Pub/Sub
o_fqdn  = "https://tediber-igkwmumuhq-ew.a.run.app"
paused  = true

image   = "gcr.io/tediber-exploitation/tediber-php:staging"

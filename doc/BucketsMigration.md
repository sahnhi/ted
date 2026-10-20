# MIGRATION BUCKETS

## DEMO

```env
GOOGLE_CLOUD_TRANSMISSION_BUCKET=balou-dev-csv-livraison
GOOGLE_CLOUD_MEDIA_BUCKET=sylius-media-demo
GOOGLE_CLOUD_INVOICE_BUCKET=sylius-invoice-demo
GOOGLE_CLOUD_QUALITY_IMAGE_BUCKET=sylius-quality-image-demo
GOOGLE_CLOUD_GENERIC_BUCKET=sylius-generic-demo
```

* [model-magnet]balou-dev-csv-livraison   >> [tediber-demo]sylius-transmission-demo
* [model-magnet]sylius-media-demo         >> [tediber-demo]sylius-media-demo-tmp         >> [tediber-demo]sylius-media-demo
* [model-magnet]sylius-invoice-demo       >> [tediber-demo]sylius-invoice-demo-tmp       >> [tediber-demo]sylius-invoice-demo
* [model-magnet]sylius-quality-image-demo >> [tediber-demo]sylius-quality-image-demo-tmp >> [tediber-demo]sylius-quality-image-demo
* [model-magnet]sylius-generic-demo       >> [tediber-demo]sylius-generic-demo-tmp       >> [tediber-demo]sylius-generic-demo


### copie des anciens buckets

* `gsutil -m rsync -r gs://balou-dev-csv-livraison gs://sylius-transmission-demo`
* `gsutil -m rsync -r gs://sylius-media-demo gs://sylius-media-demo-tmp`
* `gsutil -m rsync -r gs://sylius-quality-image-demo gs://sylius-quality-image-demo-tmp`
* `gsutil -m rsync -r gs://sylius-generic-demo gs://sylius-generic-demo-tmp`
* `gsutil -m rsync -r gs://sylius-invoice-demo gs://sylius-invoice-demo-tmp`

### copie des buckets temporaires vers les nouveaux buckets
* `gsutil -m rsync -r gs://sylius-media-demo-tmp gs://sylius-media-demo`
* `gsutil -m rsync -r gs://sylius-quality-image-demo-tmp gs://sylius-quality-image-demo`
* `gsutil -m rsync -r gs://sylius-generic-demo-tmp gs://sylius-generic-demo`
* `gsutil -m rsync -r gs://sylius-invoice-demo-tmp gs://sylius-invoice-demo`

### Strategie en qqs etapes

* je crée les buckets sur le nouveau projet (bucket final ou *-tmp)
* je lance un premier rsync sur chaque bucket (bucket final ou *-tmp) + je supprime les buckets sur l'ancien projet (à ce moment certaines features sont KOs si le service n'est pas coupé) + je les crée sur le nouveau projet
* je relance un rsync des buckets temporaires vers les buckets finaux (seulement si pas de coupure de service)


--

## STAGING

```env
GOOGLE_CLOUD_TRANSMISSION_BUCKET=csv-livraison-staging
GOOGLE_CLOUD_MEDIA_BUCKET=sylius-media-staging
GOOGLE_CLOUD_INVOICE_BUCKET=sylius-invoice-staging
GOOGLE_CLOUD_QUALITY_IMAGE_BUCKET=sylius-quality-image-staging
GOOGLE_CLOUD_GENERIC_BUCKET=sylius-generic-staging
```

* [model-magnet]csv-livraison-staging        >> [tediber-staging]sylius-transmission-staging
* [model-magnet]sylius-media-staging         >> [tediber-staging]sylius-media-staging-tmp         >> [tediber-staging]sylius-media-staging
* [model-magnet]sylius-invoice-staging       >> [tediber-staging]sylius-invoice-staging-tmp       >> [tediber-staging]sylius-invoice-staging
* [model-magnet]sylius-quality-image-staging >> [tediber-staging]sylius-quality-image-staging-tmp >> [tediber-staging]sylius-quality-image-staging
* [model-magnet]sylius-generic-staging       >> [tediber-staging]sylius-generic-staging-tmp       >> [tediber-staging]sylius-generic-staging

### copie des anciens buckets

* `gsutil -m rsync -r gs://csv-livraison-staging gs://sylius-transmission-staging`
* `gsutil -m rsync -r gs://sylius-media-staging gs://sylius-media-staging-tmp`
* `gsutil -m rsync -r gs://sylius-quality-image-staging gs://sylius-quality-image-staging-tmp`
* `gsutil -m rsync -r gs://sylius-generic-staging gs://sylius-generic-staging-tmp`
* `gsutil -m rsync -r gs://sylius-invoice-staging gs://sylius-invoice-staging-tmp`

### copie des buckets temporaires vers les nouveaux buckets
* `gsutil -m rsync -r gs://sylius-media-staging-tmp gs://sylius-media-staging`
* `gsutil -m rsync -r gs://sylius-quality-image-staging-tmp gs://sylius-quality-image-staging`
* `gsutil -m rsync -r gs://sylius-generic-staging-tmp gs://sylius-generic-staging`
* `gsutil -m rsync -r gs://sylius-invoice-staging-tmp gs://sylius-invoice-staging`

--

## PROD

```env
GOOGLE_CLOUD_TRANSMISSION_BUCKET=baloo-csv-livraison
GOOGLE_CLOUD_MEDIA_BUCKET=sylius-media-prod
GOOGLE_CLOUD_INVOICE_BUCKET=sylius-invoice-prod
GOOGLE_CLOUD_QUALITY_IMAGE_BUCKET=sylius-quality-image-prod
GOOGLE_CLOUD_GENERIC_BUCKET=sylius-generic-prod
```

* [model-magnet]baloo-csv-livraison       >> [tediber-368210]sylius-transmission
* [model-magnet]sylius-media-prod         >> [tediber-368210]sylius-media
* [model-magnet]sylius-invoice-prod       >> [tediber-368210]sylius-invoice
* [model-magnet]sylius-quality-image-prod >> [tediber-368210]sylius-quality-image
* [model-magnet]sylius-generic-prod       >> [tediber-368210]sylius-generic


### copie des anciens buckets :

```sh
gsutil -m rsync -r gs://baloo-csv-livraison gs://sylius-transmission
gsutil -m rsync -r gs://sylius-media-prod gs://sylius-media
gsutil -m rsync -r gs://sylius-invoice-prod gs://sylius-invoice
gsutil -m rsync -r gs://sylius-quality-image-prod gs://sylius-quality-image
gsutil -m rsync -r gs://sylius-generic-prod gs://sylius-generic
```

## DEV

```env
GOOGLE_CLOUD_TRANSMISSION_BUCKET=baloo-local-csv-livraison
GOOGLE_CLOUD_MEDIA_BUCKET=sylius-media-demo
GOOGLE_CLOUD_INVOICE_BUCKET=sylius-invoice-local
GOOGLE_CLOUD_QUALITY_IMAGE_BUCKET=sylius-quality-image-demo
GOOGLE_CLOUD_GENERIC_BUCKET=sylius-generic-demo
```

* [model-magnet]baloo-local-csv-livraison >> [tediber-368210]sylius-transmission-dev
* [model-magnet]sylius-media-demo         >> [tediber-368210]sylius-media-dev
* [model-magnet]sylius-invoice-local      >> [tediber-368210]sylius-invoice-dev
* [model-magnet]sylius-quality-image-demo >> [tediber-368210]sylius-quality-image-dev
* [model-magnet]sylius-generic-demo       >> [tediber-368210]sylius-generic-dev

```sh
gsutil -m rsync -r gs://baloo-local-csv-livraison gs://sylius-transmission-dev
gsutil -m rsync -r gs://sylius-media-demo gs://sylius-media-dev
gsutil -m rsync -r gs://sylius-invoice-local gs://sylius-invoice-dev
gsutil -m rsync -r gs://sylius-quality-image-demo gs://sylius-quality-image-dev
gsutil -m rsync -r gs://sylius-generic-demo gs://sylius-generic-dev
```

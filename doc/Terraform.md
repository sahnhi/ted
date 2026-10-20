TERRAFORM @Tediber
==========

## Context

Scheduled jobs on this project have been automated by Terraform. TF files can be found in folder `/terraform`.

## Bias

We've chosen to use TF modules in order to maximize simplicity of scheduled jobs configuration in `terraform/scheduled-jobs/main.tf`. In order to create, edit or delete a job on the project, the simplest way is to update this file and then push your modifications opn the repository. The CI will deploy your configuration for you.

## TF with multiple env

In order to handle multiple environments, we've configured 1 tf backend for each environment. Thus, the environment is being loaded through command `terraform init -backend-config=...`. NB: This is auto managed by the make command we've handed over.

I order to handle different states of the infrastructure between environments, you can use TF input variables in `/terraform/scheduled-jobs/tfvars/` directory. See [TF Input variables doc](https://developer.hashicorp.com/terraform/tutorials/gcp-get-started/google-cloud-platform-variables) for more info.

## Remote state

In order to prevent concurrent runs of Terraform against the same state, ours is being remotely handled in versionned GCS buckets: one for each environment. FYI it supports [state locking](https://developer.hashicorp.com/terraform/language/state/locking).

## Sticky note

1. **TF State must be up to date at all times!** 
    * Resources **managed by terraform** should never be updated through any other way than Terraform. If a resource has been modified by hand, then it is important to update the repo matching your modification so that TF state can be up to date at all times.
2. If you want to work outside of the docker compose configuration we've handed you over, then always checkout in the same directory as the `main.tf` you want to work on.

## Links

* [Terraform overview](https://developer.hashicorp.com/terraform/intro)
* [Terraform with GCP Guide](https://registry.terraform.io/providers/hashicorp/google/latest/docs/guides/getting_started)
* [GCP Authentication with TF](https://registry.terraform.io/providers/hashicorp/google/latest/docs/guides/provider_reference#authentication)
* [TF Input variables](https://developer.hashicorp.com/terraform/tutorials/gcp-get-started/google-cloud-platform-variables)
* [TF resource Cloud Scheduler Job ](https://registry.terraform.io/providers/hashicorp/google/latest/docs/resources/cloud_scheduler_job)

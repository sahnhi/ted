terraform {
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "4.46.0"
    }
  }
  backend "gcs" {
    prefix = "cloud-scheduler.tfstate"
  }
}

provider "google" {
  project = var.project
  region  = "europe-west1"
}

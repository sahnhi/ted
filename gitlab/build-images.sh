existing_tags=$(gcloud container images list-tags --filter="tags:$CI_COMMIT_SHA" --format=json "$IMAGE_REPO")

if [[ "$existing_tags" == "[]" ]]; then
  gcloud builds submit --config=gcp/run/cloudbuild.yaml --substitutions _IMAGE="$IMAGE",_IMAGE_WORKERS="$IMAGE_WORKERS",_IMAGE_REPO="$IMAGE_REPO"
else
  echo "Images with tag $CI_COMMIT_SHA already exist"
fi

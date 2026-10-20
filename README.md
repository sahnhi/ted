TED-Sylius
==========

You can find JIRA project management <a href="https://tediber.atlassian.net/jira/software/c/projects/TED/boards/5">here</a>.

## Requirements

- MacOS X or Linux
- Docker and docker compose

## Install project

Clone project:

```bash
git clone git@gitlab.com:tediber/TED-Sylius.git
```

Edit env file with your local config

- Get .env.local from your colleagues
- Set your email on "MAILER_RECIPIENT" variable in .env.local (**every** mails will be sent to this address)

### GCP credentials

Fetch GCP client credentials to be able to query GCP APIs (buckets, ...)

- Use fake credentials **(recommended)** eg. if you're not working on GCP features. Run
    ```sh
    cp app/fixtures/gcp/client_credentials.json app/
    ```

- Use credentials from the dev infra
    - Install and setup gcloud on your local environment. Be sure to configure dev as your default `core/project` then
      run
      ```sh
      gcloud secrets versions access latest --secret=tediber-client-credentials > app/client_credentials.json
      ```
    - Or retrieve them from <a href="https://console.cloud.google.com/security/secret-manager?project=tediber-dev">here</a> and copy them in your client_credentials.json file

## Build project

Build dockers

```bash
make start
```

Install assets

```bash
docker compose exec nodejs yarn install && yarn build
```

Note for MAC users, all make commands need to be called passing MAC variable eg

```bash
make start MAC=1
```

To generate your secrets for API Platform

```bash
php bin/console lexik:jwt:generate-keypair
setfacl -R -m u:application:rX -m u:"$(whoami)":rwX config/jwt
setfacl -dR -m u:application:rX -m u:"$(whoami)":rwX config/jwt
```

## Import database

Download last "tediber_demo_light_XXXXXX" database
from <a href="https://console.cloud.google.com/storage/browser/model-magnet-209308.appspot.com/databases;tab=objects?pageState=(%22StorageObjectListTable%22:(%22f%22:%22%255B%255D%22))&hl=fr&project=model-magnet-209308&prefix=&forceOnObjectsSortingFiltering=false">
storage</a>

Copy database and script to mysql docker

```bash
docker cp $pathToExport db:/home/
```

Connect to mysql container

```bash
docker exec -it db /bin/bash
```

Import database and execute script

```bash
mysql -u $username -p tediber < /home/database.sql
```

## Debug

To use debug containers such Ngrok, you need to launch the debug profile using
docker compose or the Makefile.

```bash
docker compose --profile debug up -d
make start-debug
```

### Use redis

In your terminal

```bash
docker exec -it redis /bin/bash
redis-cli
MONITOR
```

In another tab

```bash
docker exec -it php-nginx /bin/bash
bin/console messenger:consume mail -vv
```

### Ngrok

Ngrok allows to expose your local app to the internet for payment Api tests mostly.

To use is you need to create a free account <a href="https://ngrok.com/">here</a> and paste your auth token in
.env.local file.
The interface can be found <a href="http://127.0.0.1:4040">here</a>

## Deployment

All deployments are handled with Gitlab-CI except for the CRON configuration.
Before pushing your code, you can validate some tests

### Deploy Scheduled jobs

To deploy Scheduled jobs TF infrastructure code, use TF make commands:

```bash
make tf-init <choose an environment>
make tf-plan <choose an environment>
make tf-apply <choose an environment>
```

## Tests

### Functional tests

If never been done, or if you get an error stating that the database does not exist

```bash
make init-test-functional
```

Execute tests

```bash
make test-functional
```

### Unit testing

```bash
make test-unit
```

Report is available at file:///{PROJECT_PATH}/coverage/index.html

### Php CS

```bash
make test-cs
```

# Connect to Cloud Sql

This documentation will guide you to connect to a Cloud Sql instance from your local computer.

This procedure means that you will be connected to live instances of production databases. Act with care...

## Prerequisites

- Permissions on the project you want to connect to (ask for them to the person in charge if it is not the case)
- A Mysql Client of your choosing, I recommend the following
  - <a href="https://dbeaver.io/">Dbeaver</a>
- Warning : CloudSqlProxy version might be upgraded since this documentation was made. Make sure to upgrade the documentation or contact the documentation creator to do it.

## Install and setup

In the following example we will use tediber-demo project as an example

You will need to install the following libraries
- <a href="https://cloud.google.com/sdk/docs/install">Gcloud CLI</a>
- <a href="https://cloud.google.com/sql/docs/mysql/sql-proxy#install">Cloud Sql Proxy</a>

Once this is done you need to initialise your configuration with the project you want to connect to
```shell
gcloud init
```

## Connect your host to Cloud Sql

The following command will open a proxy between the tediber-demo instance and your localhost on port 1234
```shell
./cloud-sql-proxy --address 0.0.0.0 --port 1234 tediber-demo:europe-west1:tediber
```
Now all you have to do is use your favorite MySql and connect to the database with the credentials that you can find in Gitlab-CI

If you encounter any authentication issue while running the command, login with this
```shell
gcloud auth application-default login
```

## Connect your container (local app) to Cloud Sql

### Setup your container

- Be sure you are on the branch with the migrations to execute
- Comment the automatic migrations in docker-entrypoint-dev.sh
- Change the database configurations in .env (or .env.local)
- Make sure to put 0.0.0.0 for MYSQL_HOST
- Create a ProxySQL folder at the project root
- Paste cloud-sql-proxy and credentials.json in that folder
- Restart and build your container
    ```shell
    docker compose down
    docker compose up -d --build
    ```

### Launch proxy
```shell
docker exec -it php-nginx /bin/sh
cd ProxySQL
./cloud-sql-proxy -a 0.0.0.0 -p 3306 tediber-demo:europe-west1:tediber -c credentials.json
```

### Execute migrations if needed

In another window
```shell
docker exec -it php-nginx /bin/sh
```
From here you can execute the desired migrations or all of them

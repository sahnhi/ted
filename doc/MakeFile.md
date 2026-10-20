# MakeFile Description

## Project Installation

Start your project (if it's the first time, it will also build it).

```shell
make start
```

## Project Usage

Stop your project (stop docker containers)

```shell
make stop
```

Restart your project (does a `make start` and `make stop`)

```shell
make restart
```

## Project Re-initialisation

In case you have trouble with your project (vendors issues,
docker issues ...) you can make it clean without using
docker-prune wich completely erase all your docker containers
and dependencies (might be bad if you run multiple projects on
the same machine)

This makes a `make stop` then remove dependencies files (vendor, assets, ...)

```shell
make clean
```

You can also do the same and then remove all docker containers to start fresh

```shell
make clear
```

In case you need to restart entirely your project just use the following:

```shell
make clear && make start
```

You can do the same + force recreation of your containers

```shell
make clear && make build
```

## More options

Use this command to find out all available commands in the Makefile

```shell
make
```

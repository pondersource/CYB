# CYB
PHP code to Connect Your Books

Under construction.

This gateway will let you connect various data sources and sinks for bookkeeping data in the domains of:
* time tracking
* issue tracking
* invoicing
* mutual credit networks
* budget tracking

The name CYB stands for "Connect Your Books".

See [Federated Bookkeeping](https://federatedbookkeeping.org) for more info on the vision behind it.

## Usage

```sh
cd cyb-app
docker compose up -d
```

Or:
```sh
cd cyb-app
./debug.sh
./clean.sh # careful! This kills all your containers, also unrelated ones!
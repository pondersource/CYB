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

### for developing on local machine:
```sh
cd cyb-app
chmod u+x ./run-docker.sh
./run-docker.sh dev pull
./run-docker.sh dev up

```

### for developing on Gitpod:
```sh
cd cyb-app
chmod u+x ./run-docker.sh
./run-docker.sh gitpod pull
./run-docker.sh gitpod up
```

### for deploying on VPS:
```sh
cd cyb-app
chmod u+x ./run-docker.sh
./run-docker.sh prod pull
./run-docker.sh prod up
```

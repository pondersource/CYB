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
./run-docker.sh dev up --detach
```

### for developing on Gitpod:
```sh
cd cyb-app
chmod u+x ./run-docker.sh
./run-docker.sh gitpod pull
./run-docker.sh gitpod up --detach
```

### for deploying on VPS:
```sh
cd cyb-app
chmod u+x ./run-docker.sh
./run-docker.sh prod pull
./run-docker.sh prod up --detach
```

## Development
### How to add a new connector
1. Pick a name for your connector. Must be all lowercase and url safe. Ex. time_mld, github
2. Create a new folder under `app/Connectors` and name it in the studly form of your connector name. Ex. TimeMld, Github
3. Put an implementation of `\app\Core\Connector` in the newly created folder. Ex. GithubConnector.php
4. Modify `\app\Core\ApplicationManager::getConnectors` and add an instance of your Connector to it.

### Routes
In order to support custom routes for your connector, create a folder named `routes` under your connector's root folder. Then you can create `api.php` and `web.php` files there to define your routes. The newly added routes will be under `/api/connector/{connector name}` and `/connector/{connector name}` respectively. And the routes can be addressed under the name `connector.{connector name}`.

### Returning views for your connector web routes
Suggested approach is to create a `resources\views` folder under your connector's root folder. Then you can reference those views by using the View facade (`Illuminate\Support\Facades\View`) and calling `View::file` method with the full address to your view file.
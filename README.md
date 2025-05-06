# RightMart Test

## Quick Start
### Source
Pull the source code from the following GitHub repository:
```
git@github.com:farzan/rightmart_test.git
```

Or use this in terminal:
```sh
git clone git@github.com:farzan/rightmart_test.git
```


### Build the project
Run the following command in the root of the project:
```sh
make setup
```
This command will take care of everything for building.

### Index the sample file
Run the following command to index the sample file:
```sh
make ingest
```
This command ingest the sample file and waits for more logs to be appended. 
You can stop at any time by pressing CTRL+C. 

### Query result
Per project's requirements, an endpoint is available to test the processed log entries. 
For the tester's convenience, a small dashboard is available to run the tests. you can access
it via link below:

http://localhost


### Tear down the project
The following command stops the project, removes all data, docker volumes, related docker network etc.
You can use this to reset the project to the start.
```
make teardown
```

## Overview
This project is developed using the following technologies:
 * Docker and Compose
 * PHP 8.3
 * Symfony 7.2
 * ElasticSearch 8
 * Logstash 8
 * NginX

The software reads the log file `assets/logs.log`, parses the entries, and write them
in ElasticSearch through Logstash. Stream positions are stored in a file database stored
in `var/storage.json`, so if user stops the script, it can be resumed later. The API
endpoint is:

http://localhost/count

User can use a dashboard to use the API here:

http://localhost


## Components

### Source code 
Symfony 7.2 is used to develop this software. I have used Hexagonal/Ports and Adapters architecture
to separate concerns. In the folder `src/`, you see the following directories:
 * **Domain**: Contains models related to the domain,
 * **Application**: Contains ports, and service implementations of the application,
 * **Adapter**: Implementations of ports.

Using this architecture helps to explicitly separate business logic from implementation details,
such as presentation, and persistence.

**Alternative solution:** _Layered architecture_ (presentation/business logic/data access) is an alternative
to the architecture, and can be used in a small software like this. The decision depends on the team's 
decisions and conventions.

### ElasticSearch and Logstash
I decided to use ElasticSearch because the as the requirements state that the log file is
of Tera Bytes of data. So the assumption could be soon we will hit the single server hardware
limits and need to scale horizontally. ElasticSearch allows scaling out of the box and is
a very good choice for storing huge amount of log data.

**Alternative solution:** Any other database could be used. To choose a database, we need to have
the possibility and ease of scaling in mind.

Logstash is used in this software as a pipeline between PHP application and the ElasticSearch. Of cource
I could directly use ElasticSearch API from PHP, but there would be issues to handle:
- Handling failed inserts,
- Handling backpressure,
- Handling bulk insertion, as inserting terabytes of log one by one takes a lot of time.

All of these issues could be implemented in the PHP code, but would take a lot of time.

**Alternative solution:** PHP directly uses ElasticSearch API. For better performance, software
should use the ES bulk API, and create a buffer for it.

#### ElasticSearch index mapping
Although ElasticSearch permits to use indices without explicit mapping/schema,
but it is recommended to do so. I have added a mapping template for the default 
index, so the ElasticSearch does not do full text analysis on the data, as we 
are looking up only keywords, and doing comparisons.

#### Logstash connection
I use sockets to connect to the Logstash, to avoid overhead of making multiple 
connections.

### Docker and Compose
Project leverages Docker Compose to manage multiple services. Here is the list 
of services:

| Service       | Description                                   | Is Up? |
|---------------|-----------------------------------------------|:------:|
| php-builder   | Doing development tasks and running tests.    |   No   |
| php-cli       | Running CLI application                       |  Yes   |
| php-fpm       | Running Web application                       |  Yes   |
| nginx         | Web server                                    |  Yes   |
| elasticsearch | Database                                      |  Yes   |
| logstash      | Pipeline between CLI application and Database |  Yes   |

#### Why multiple PHP images?
There are 3 different PHP images in the project. Each is fine-tuned to do a
specific task. For example `php-cli` image has the `pcntl` PHP extension to
manage system signals. `php-fpm` does not have that extension because there's
no use of it in controllers. `php-builder` is added to help development of
software, installing packages, test and debugging etc.

### Makefile
This project utilizes a make file to keep frequently used commands. Here is the
list of make file targets:

| Target               | Description                                                                                         |
|----------------------|-----------------------------------------------------------------------------------------------------|
| setup                | Builds the entire project from ground up, to the point that it is ready to use                      |
| teardown             | Removes data, downloaded packages, logs and caches, and docker network                              |
| build                | Builds docker images                                                                                |
| composer-install     | install PHP packages using `composer`                                                               |
| up                   | Ups the containers                                                                                  |
| down                 | Downs the docker containers                                                                         |
| ingest               | Ingest the log entries in the log file, and waits for new iterms                                    |
| ingest-no-tail       | Ingest the log entries in the log file, and terminates the command when reaches the end of the file |
| reset-file-database  | Resets the database that keeps the position of each file stream                                     |
| test                 | Runs tests; Unit and Integration                                                                    |
| shell-builder        | Creates an interactive shell with the `php-builder` image                                           |
| shell-cli            | Creates an interactive shell connected to the `php-cli` container                                   |
| shell-fpm            | Creates an interactive shell connected to the `php-fpm` container                                   |
| shell-nginx          | Creates an interactive shell connected to the `php-nginx` container                                 |
| compose-debug        | Debug docker-compose.yaml file                                                                      |                                                                     |
| compose-ps           | List containers of the current compose                                                              |
| healthcheck-elastic  | Health-checks the ElasticSearch server                                                              |
| healthcheck-logstash | Health-checks the Logstash server                                                                   |
| cache-clear          | Clears the cache of Symfony                                                                         |

### Tests
There are Unit and Integration tests in the project. You can run them using the following command:
```
make test
```

### Troubleshooting
If you cannot run the project, following tips may be useful:
* In case of trouble, always tear down the project and set it up again:
```
make teardown
make setup
```
* Sometimes restarting Docker Desktop or Docker services solves some caching issues.
* Sometimes it takes a lot of time for ElasticSearch to boot up. If it takes more than 120 seconds, the setup script will fail.
* Make sure you don't have other containers/web servers that use the port `80`.


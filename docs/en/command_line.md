Command line
=============

Run
----

run command to run the consumer program.

```php
$ vendor/bin/mqk run --config path
```

Options
--------

`--config`

> vendor/bin/mqk run --config config.ini

Specify the configuration file path, run a small option has a corresponding configuration items. Use a configuration file to avoid a large number of very long command line parameters.

`-vvv`

Set the description level of mqk, the higher the level, the more detailed the content. Excessive level of log output will affect the performance of the program.

```php
$ vendor/bin/mqk run -v
```

```php
$ vendor/bin/mqk run -vv
```

```php
$ vendor/bin/mqk run -vvv
```

`--bootstrap`

Initialize the path of the program, you can monitor the event in the initialization process or do the initialization configuration.

`--concurrency` `-c`

Set the number of processes of the process of the consumer process, the process of about the performance of about an appointment, according to the specific business to fine-tune.

`--redis`

> --redis redis://:password@host:port/db

DSN of redis

`--fast`

Speed mode will cancel the message timeout retry mechanism. Suppose a message is in the process of downtime or overtime specified in the process. MQK
Will be re-issued in this message, to ensure that the message was executed at least once.

`--sentry`

Set the sentry's DSN. sentry is a service that captures exception information and sends it to the console.

`--burst`

burst mode after the completion of the queue after the completion of consumption.
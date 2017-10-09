Config
=======

MQK uses yaml as a configuration file to hold configuration file information as a dictionary. Specific configurable items include:

- Redis connection
- Concurrency
- Logging
  - Logging handlers
  - Logging level
- Fast mode
- Burst mode
- Sentry dsn
- Boostrap

```yaml
queues:
  - default
  - low
redis: redis://:123@192.168.0.1:3270/1
concurrency: 10
logging:
  handlers:
    - StreamHandler

burst: false

fast: false
bootstrap: bootstrap.php
sentry: http://055e1909eeed4d56b45d7a5091bd04bc:3d41f3f3eddf419a8e7bd7816da0054d@sentry.com/16
```

Queue
-----

Configure the queues used by the consumer process, refer to the [Advanced Options] (advanced_options.md)

```yaml
queues:
  - default
  - low
```

Redis
-----

Set the Redis instance used by MQK.

```
# config.yml 

redis: redis://:123@192.168.0.1:3270/1
```

Concurrency
------------

Configure the number of child processes, expressed as a number. On the operation of the process of reference [process document] (process.md)

```
# config.yml
concurrency = 10
```

Logging
--------

Configure the default logging level

```
# config.yml
logging:
  level: DEBUG
```

handlers
--------

Configure Monolog's Handlers, using StreamHandler by default. You can choose any Handler, such as Graylog Handler can log output
To graylog.

StreamHandler configuration example

1. The default output to STDOUT

The default setting uses STDOUT, or it can be configured into the Log file.

```
# config.yml
logging:
  handlers:
    - StreamHandler
```

More config

```
# config.yml
logging:
  handlers:
    -
      class: StreamHandler
      level: INFO
      arguments: php://stdout
```

2. Output to the log file

```
# config.yml
logging:
  handlers:
    -
      class: StreamHandler
      level: INFO
      arguments: app.log
```

3. Output to the graylog

```
# config
logging:
  handlers:
    -
      class: GelfHandler
      level: INFO
      arguments: ['127.0.0.1', 50002]
```

Bootstrap
----------

The contents of the file path, specify a PHP file, each child will be introduced when the process is open. You can configure the monitoring of events here or do some initialization.

```
# config.yml

bootstrap: bootstrap.php
```

Sentry
------

In the production environment, some important anomaly data needs to be captured. sentry configuration sentry dsn.

```
# config.yml
sentry: http://055e1909eeed4d56b45d7a5091bd04bc:3d41f3f3eddf419a8e7bd7816da0054d@sentry.com/16
```

Burst mode
-----------

In Burst mode, if the message queue is empty, the consumer process exits the process after the task completes. When all the child processes exit, the main integration is immediately out.

```
# config.yml
burst: true
```

Fast mode
---------

In Fast mode, if the process fires and the message is not processed properly, the message will be lost. When fast mode is enabled, it is guaranteed that the message is executed at least once.
Enabling Fast mode gives you better performance because it will save a lot of Redis instructions.

In a version, fast mode performance can be increased by 5 times.

```
# config.yml
fast: true
```

Non-fast mode saves the message that is currently being processed in an ordered collection. And the message is serialized in KV. When the message processing time is too long, resulting in no
The specified timeout period is removed from the ordered collection. Then, will think that the task timeout fails, will be a retry.

Retry
-----

Number of retries, defaults to 3 times. Refer to fast mode for details.

```
# config.yml
retry: 3
```
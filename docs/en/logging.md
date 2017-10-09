Logging handle
===============

When the application is developed, the execution log data of the program is a very important message. When the program running on the line is abnormal, we need very log information to understand the status of the application.

StreamHandler defaults to outputting the log to the console.

```yaml
logging:
  handlers:
    - StreamHandler
```

`$logger->debug` Outputs debug information at debug level.

```
$logger = LoggerFactory::shared()->getLogger(__CLASS__);
$logger->debug("Hello");
```

GelfHandler
-------------

In a real production environment, we can not go to the server to view the log information, although Monolog support the log output to the file. Usually we will log out some distributed focus
Of the log server, here to Graylog distance.

```yaml
logging:
  handlers:
    - GelfHandler: {host: 127.0.0.1, port: 50002}
```

More Handler can view Monolog's documentation.
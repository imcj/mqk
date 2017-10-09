Error handle
=============

MQK consumers will catch `Exception` and output at the console. You can also use sentry to catch exceptions.

Sentry
-------

Start `vendor/bin/mqk --config config.yml` to specify the configuration file and add the sentry entry.

```
# config.yml

sentry: sentry_dsn
```

Or by command-line arguments

```shell
$ vendor/bin/mqk --sentry sentry://host
```


Error handle
-------------

Set the `error_handlers` list in the configuration file, one for each class name, and implement the `MQK\Error\ErrorHandler` interface.

```yaml
error_handlers:
  - App\ExceptionHandler
```

```
use MQK\Error\ErrorHandler;

class ExceptionHandler implements ErrorHandler
{
    public function got(\Exception $exception)
    {
        
    }
}
```

Crash restart
--------------

If the consumer process is running for some reason, the main process will start a process again. On Unix systems, the process exit triggers a SIGCHLD signal,
When the main process listens for the signal, the child process exits when it starts a new process and continues to work.

Error retry
------------

The message processing in the MQK has a default timeout, and when this time is exceeded, the MQK considers the message to fail. MQK if accidentally crash or host
Downtime, in order to avoid downtime as a message is lost.

MQK writes each message to a list of executions, and the message is deleted from the list after it is finished. If there is a message that has not been deleted over time, MQK will think
The process of dealing with the consumer has been running away.

You can retry the global timeout timeout in the configuration file.

```
# config.yml
fast: false
```

或者

```php
$message = \K::invokeLate('Calculator::sum', 1, 1);
$message->setRetry(5)
```
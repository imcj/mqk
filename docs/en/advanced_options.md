Advanced options
=================

Use the command line arguments to specify the processed queues. `vendor / bin / mqk --queue default --queue low`, in order of priority.

MQK priority processing is very simple, in the above example, when the contents of the `default` queue after consumption, will be taken from the` low` queue, if the `default` queue
There is always a message, then the `low` queue may never be executed. Of course, is not absolute, the specific details can be mentioned in the following.

Config file
------------

```yaml
queues:
  - default
  - low
```

Queue
-------

The value of the `$ queue =" default "` property is `default`, and the execution queue for the function execution will enter the default queue.

```php
class Caculator
{
    public static $queue = "default";
}
```

Concurrency
-------------

MQK is based on multi-process mode execution, and it is well known that PHP threads have not been well supported at the language level. There is a problem with data contention between threads. MQK uses the `workers` parameter
Specifies the number of process starts. Multi-process can effectively use the performance of multi-core server, in a real business environment, a single machine CPU has a very high utilization rate.

Another situation with multiple process models is that a single process can only process one message at a time, resulting in poor concurrency. The number of specific processes needs to be adjusted according to the actual situation.

Every consumer a process, consumers use redis `blpop` from the queue to take a data, the redis connection is blocked. The message in redis is json format, the consumer
The json is assembled into a Message object, and the Message object saves the context parameters with the payload header.

```yaml
concurrency: 20
```
MQK
====

[简体中文](RADME_CN.md)

By google translate

MQK is a simple, high-performance PHP background task framework. MQK simplifies complex message queues to RPC and event handling, eliminating the need for complex queues, tasks, and processes.
MQK can handle `20,000 +` data per second on a single core VPS, and `6,000 +` per second in fault tolerant mode.

Install
--------
```
composer require fatrellis/mqk
```

Dependencies
---------------

- php 5.6
- redis-server

Usage
------

1. The first step is to use the `K :: invoke` method to call the method and pass in the parameter` \ K :: invoke ('\\ MQK \\ Test \\ Calculator :: sum', 1, 2) `.


```php
\K::invoke('Calculator::sum', 1, 2);
```

2. Create class and static methods

```php
class Calculator
{
    public static function sum($a, $b)
    {
        return $a + $b;
    }
}
```

3. Start the consumer program. In debug mode, the console outputs the return result of the asynchronous task.

```
$ vendor/bin/mqk run
[2017-07-11 08:14:52] 14327 .NOTICE: Master work on 14327 [] []
[2017-07-11 08:14:56] 14331 .INFO: Message finished and result is 2 [] []
```


Development status
-------------------

Development, is not recommended for use in the production environment.

- No rigorous testing, there may be a variety of problems.
- Function parameters in json format serialization, can not use php objects


Documents
----------

It is recommended to use MQK's event mechanism to perform real-time data analysis.

- [Getting started](docs/getting-started.md)
- [Basic](docs/basic.md)
- [RPC](docs/rpc.md)
- [Event](docs/event.md)
- [Best practices](docs/practices.md)
- [Config](docs/config.md)
- [Error handle](docs/error.md)
- [Advance options](docs/advanced_options.md)
- [Process health](docs/process_health.md)
- [Logging](docs/logging.md)
- [Signals](docs/signals.md)
- [Sharding](docs/sharding.md)
- [Command line](docs/command_line.md)
- [Process management](docs/process.md)

- [新手入门](docs/cn/getting-started.md)
- [基本使用说明](docs/cn/basic.md)
- [RPC](docs/cn/rpc.md)
- [事件](docs/cn/event.md)
- [最佳实践](docs/cn/practices.md)
- [配置](docs/cn/config.md)
- [错误处理](docs/cn/error.md)
- [高级选项](docs/cn/advanced_options.md)
- [进程健康状态](docs/cn/process_health.md)
- [日志](docs/cn/logging.md)
- [信号](docs/cn/signals.md)
- [扩容](docs/cn/sharding.md)
- [命令行说明](docs/cn/command_line.md)
- [进程管理](docs/cn/process.md)
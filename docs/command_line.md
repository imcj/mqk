命令行说明
==========

Run
----

run命令运行消费者程序。

```php
$ vendor/bin/mqk run --config path
```

选项
----

`--config`

> vendor/bin/mqk run --config config.ini

指定配置文件路径，run命令小的选项都有对应的配置项。使用配置文件避免大量的很长的命令行参数。

`-vvv`

设定mqk的描述级别，级别越高，内容越详细。过高级别的日志输出会影响程序的性能。

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

初始化程序的路径，可在初始化程序中监听事件或者是做初始化配置。

`--workers`

设定消费者进程的进程数量，理论上进程约多性能约好，根据具体的业务来微调。

`--redis`

> --redis redis://:password@host:port/db

redis连接的DSN。

`--fast`

极速模式将取消消息的超时重试机制。假设一个消息在处理的过程中宕机或者超时指定的时间。MQK
会在此重发这个消息，保证消息最少被执行一次。

`--sentry`

设置sentry的DSN。sentry是一个捕捉异常信息并发送到控制台的服务。

`--burst`

burst模式下队列消费完成后进程退出。
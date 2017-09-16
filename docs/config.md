配置
====

MQK使用yaml作为配置文件，以字典形式保存配置文件信息。

```yaml
redis: redis://:123@192.168.0.1:3270/1
workers: 10
logging:
  handlers:
    - StreamHandler

# 参考burst模式
burst: false

# 参考fast模式
fast: false
bootstrap: bootstrap.php
sentry: http://055e1909eeed4d56b45d7a5091bd04bc:3d41f3f3eddf419a8e7bd7816da0054d@sentry.com/16
```

Redis
-----

设置MQK使用的Redis实例。

Workers
-------

配置子进程数量。参考[多进程文档](multi-process.md)

Logging
--------

handlers
--------

配置Monolog的Handlers，默认使用StreamHandler。可以选择任意的Handler，例如 Graylog 的Handler可以把日志都输出
到 graylog 中。

Bootstrap
----------

内容为文件路径，指定一个PHP文件，每个子进程开启的时候会引入。可以在这里配置事件的监听或者做一些初始化操作。

Sentry
------

在生产环境中，一些重要的异常数据需要捕获。sentry 内配置 sentry dsn。

Brust模式
---------

Brust模式中消息队列如果为空，消费者进程在任务完成后会退出进程。当所有的子进程退出后，主集成也随即对出。

Fast模式
---------

Fast模式下如果进程奔溃导致消息没有正常处理完成，该条消息会被其他的消费者进程继续处理。如果启用fast模式，不保证消息已定正确处理，
但是因为减少了Redis的存取，可以获得更好的性能。

在某个版本中，fast模式的性能可以提高5倍。
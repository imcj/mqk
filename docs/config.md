配置
====

MQK使用yaml作为配置文件，以字典形式保存配置文件信息。具体可配置项包括：

- Redis连接
- 消费者进程数量
- 日志
  - 日志处理器
  - 日志级别
- fast模式
- burst模式
- sentry dsn
- 初始化程序

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

```
# config.yml 

redis: redis://:123@192.168.0.1:3270/1
```

Workers
-------

配置子进程数量，以数字表示。参考[多进程文档](multi-process.md)

```
# config.yml
workers = 10
```

Logging
--------

配置默认打印级别

```
# config.yml
logging:
  level: DEBUG
```

handlers
--------

配置Monolog的Handlers，默认使用StreamHandler。可以选择任意的Handler，例如 Graylog 的Handler可以把日志都输出
到 graylog 中。

StreamHandler配置示例

1. 默认输出到 STDOUT

默认设置使用STDOUT，也可以配置到Log文件中。

```
# config.yml
logging:
  handlers:
    - StreamHandler
```

更多的配置

```
# config.yml
logging:
  handlers:
    -
      class: StreamHandler
      level: INFO
      arguments: php://stdout
```

2. 输出到日志文件

```
# config.yml
logging:
  handlers:
    -
      class: StreamHandler
      level: INFO
      arguments: app.log
```

3. 输出到Graylog

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

内容为文件路径，指定一个PHP文件，每个子进程开启的时候会引入。可以在这里配置事件的监听或者做一些初始化操作。

```
# config.yml

bootstrap: bootstrap.php
```

Sentry
------

在生产环境中，一些重要的异常数据需要捕获。sentry 内配置 sentry dsn。

```
# config.yml
sentry: http://055e1909eeed4d56b45d7a5091bd04bc:3d41f3f3eddf419a8e7bd7816da0054d@sentry.com/16
```

Brust模式
---------

Brust模式中消息队列如果为空，消费者进程在任务完成后会退出进程。当所有的子进程退出后，主集成也随即对出。

```
# config.yml
burst: true
```

Fast模式
---------

Fast模式下如果进程奔溃导致消息没有正常处理完成，消息将会丢失。启用fast模式后，保证消息最少正确执行一次。
启用Fast模式可以获得更好的性能，原因是将节省很多Redis的指令。

在某个版本中，fast模式的性能可以提高5倍。

```
# config.yml
fast: true
```

非fast模式，会在一个有序集合中保存当前正在处理的消息。并且会将消息序列化保存在KV中。当消息处理时间过长，导致没有在
指定的超时时间中从有序集合中移除。那么，会认为该任务超时失败，会进行一次重试。

重试
----

重试次数，默认为3次。具体描述参考fast模式。

```
# config.yml
retry: 3
```
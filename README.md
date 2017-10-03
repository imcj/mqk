MQK
====

MQK是一个简单、高性能的PHP后台任务框架。MQK把复杂的消息队列简化成RPC和事件处理，不需要关心复杂的队列、任务和进程等。
MQK在单核心的VPS上每秒可以处理`20,000+`数据量，在容错模式下每秒可处理`6,000+`。

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

1. 第一步，使用`K::invoke`方法调用方法并传入参数`\K::invoke('\\MQK\\Test\\Calculator::sum', 1, 2)`。

```php
\K::invoke('Calculator::sum', 1, 2);
```

2. 创建任意的类文件和静态方法

```php
class Calculator
{
    public static function sum($a, $b)
    {
        return $a + $b;
    }
}
```

3. 启动消费程序。debug模式下控制台会输出异步任务的返回结果。

```
$ vendor/bin/mqk run
[2017-07-11 08:14:52] 14327 .NOTICE: Master work on 14327 [] []
[2017-07-11 08:14:56] 14331 .INFO: Message finished and result is 2 [] []
```


开发状态
--------

开发中，不推荐用在生产环境中使用。

- 未进行严格的测试，可能存在各种问题。
- 函数参数以json格式进行序列化，不能使用php对象


文档
====

推荐使用MQK的事件机制可以进行实时数据计算分析。

- [新手入门](docs/getting-started.md)
- [基本使用说明](docs/basic.md)
- [RPC](docs/rpc.md)
- [事件](docs/event.md)
- [最佳实践](docs/practices.md)
- [配置](docs/config.md)
- [错误处理](docs/error.md)
- [高级选项](docs/advanced_options.md)
- [进程健康状态](docs/process_health.md)
- [日志](docs/logging.md)
- [信号](docs/signals.md)
- [扩容](docs/sharding.md)
- [命令行说明](docs/command_line.md)
- [进程管理](docs/process.md)
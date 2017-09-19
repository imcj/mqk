高级选项
========

使用命令行参数指定处理的队列。`vendor/bin/mqk --queue default --queue low`，按照先后顺序处理优先级。

MQK的优先级处理非常简单，在上面的例子中，当`default`队列的内容消费完后，会从`low`队列中取，如果`default`队列
一直存在消息，那么`low`队列可能永远都执行不到。当然，也不是绝对，具体细节在下面可以讲到。

配置文件配置
--------

```yaml
queues:
  - default
  - low
```

指定处理队列
-----------

定义`$queue = "default"`属性的值为`default`，函数执行的执行队列将进入default队列。

```php
class Caculator
{
    public static $queue = "default";
}
```

并发
----

MQK基于多进程的模式执行，总所周知，PHP的线程一直没有在语言级别有很好的支持。线程间存在数据的争用等问题。MQK使用`workers`参数
指定进程启动的数量。多进程可以有效的利用多核服务器的性能，在真实的业务环境中，单台机器的CPU基本都有很高的利用率。

使用多进程模型的另外一种情况是单个进程一次只能处理一个消息，导致并发能力比较差。具体的进程数量需要根据实际情况进行调整。

每一个消费者一个进程，消费者使用redis的`blpop`从队列中取一条数据，这个redis连接是阻塞式的。消息在redis中是json格式，消费者
将`json`装配成`Message`对象，`Message`对象用`payload`字端保存上下文参数。

```yaml
workers: 20
```
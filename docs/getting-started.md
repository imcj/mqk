新手入门
========

异步RPC
-------

1. 安装composer包

```php
$ composer require fatrellis/mqk
```

2. 定义异步RPC方法
```php
class Calculator
{
    public static function sum($a, $b)
    {
        return $a + $b;
    }
}
```

3. 启动消费者进程

-vvv 参数打印详细日志

```php
$ vendor/bin/mqk run -vvv
```

4. 执行异步RPC

```php
\K::invoke('Calculator::sum', 1, 2);
```

更多
----

异步方法这种RPC模式比较适合像邮件发送这种模式。当我们需要对系统进行解耦，例如某一个资源A发生变化，另外一个资源B因依赖资源A需要维护数据一致性。
推荐派发一个A资源的修改事件，然后在事件处理中维护数据的一致性。

[事件](event.md)

进阶使用

进阶使用描述了如果处理多个不同的队列，介绍了MQK中如何有效的利用并发特性。

[进阶使用](advanced_options.md)
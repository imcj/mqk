MQK
====

MQK是一个创建后台任务的轻量级的消息队列应用。使用`K::invoke`方法直接调用php方法。

定义一个需要在后台运行的函数。

```
function sum($a, $b)
{
    sleep(1);
    return $a + $b;
}
```

使用`K::invoke`调用sum函数。
```
$job = \MQK\K::invoke('sum', 1, 2);
sleep(1);

assert(3 === (int)$job);
```

项目目录下运行mqk run.

```
$ vendor/bin/mqk run
Master work on 14360
Process 14364 started.
```

## Install

```shell
$ composer require fatrellis/mqk
```

## 依赖

- php 5.6
- php-redis
- redis-server
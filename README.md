MQK
====

MQK是一个轻量级的消息队列应用。

一个需要耗时长的函数。
```
function sum($a, $b)
{
    sleep(1);
    return $a + $b;
}
```

使用`MQK::invoke`调用sum函数。
```
$job = \MQK\K::invoke('sum', 1, 2);
sleep(1);

assert(3 === (int)$job);
```

想要在后台运行队列功能，在项目目录下运行mqk.

```
$ mqk run
*** Listening
Job id a2c34 running.
```

## Install

```shell
$ composer require mqk/mqk
```
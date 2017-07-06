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

## 开发状态

开发中，不可用于生产环境

Issues

- [ ] 任务超时再次重试存在BUG

TODO

- [ ] `bin/mqk monitor` 增加redis dsn参数配置redis
- [ ] 主进程退出退出的时候先退出其他子进程
- [ ] 增加burst模式，队列为空时进程退出不在服务
- [ ] 通过信号增加和减少Worker的数量

## Install

```shell
$ composer require fatrellis/mqk
```

## 依赖

- php 5.6
- php-redis
- redis-server

## 性能测试

进行写入压力测试。

```shell
$ mqk invoke \\MQK\\Test\\Caculator::sum 1 1 --invokes 1000 --workers 10

Options
    --invokes -i 总的调用次数，例如1000次调用
    --workers -w 并发的进程数量
```
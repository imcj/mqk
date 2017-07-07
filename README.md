MQK
====

MQK是一个创建后台任务的轻量级的消息队列应用。

## 第一步
使用`K::invoke`方法调用方法并传入参数`\K::invoke('\\MQK\\Test\\Calculator::sum', 1, 2)`。

```php
function main()
{
    $job = \K::invoke('\\MQK\\Test\\Calculator::sum', 1, 2);
    sleep(1);
    
    assert(3 === (int)$job->result());
}
```

`$job->result()` 可以查询到计算的结果，异步任务一般用不到。

也可以使用命令行测试。`mqk invoke \\MQK\\Test\\Caculator::sum 1 1`

## 第二步
调用前先定义一个在后台运行的函数，计算两个参数相加。

```
function sum($a, $b)
{
    sleep(1);
    return $a + $b;
}
```

## 第三步
在项目目录下运行mqk run.

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

## 开发状态

开发中，不推荐用在生产环境

## 性能测试

进行写入压力测试。先用`invoke`命令批量写入10,000条数据。然后用`monitor`命令观察任务的观察情况。

```shell
$ mqk invoke \\MQK\\Test\\Caculator::sum 1 1 --invokes 10000 --workers 10

Options
    --invokes -i 总的调用次数，例如1000次调用
    --workers -w 并发的进程数量
```

`monitor`每秒检查并输出剩余的队列数量，可以了解到队列消费的数据情况。

```shell
$ mqk monitor
2017-07-07 03:55:07,4000
2017-07-07 03:55:08,0

$ mqk monitor --redis redis-dsn://192.168.0.100

Options
    --redis-dsn -s Redis服务器的DSN redis://127.0.0.1:1234
```

## Issues

- [ ] 任务超时再次重试存在BUG

## TODO

- [x] `bin/mqk monitor` 增加redis dsn参数配置redis
- [ ] 主进程退出退出的时候先退出其他子进程
- [x] 增加burst模式，队列为空时进程退出不在服务
- [ ] 通过信号增加和减少Worker的数量
- [ ] Redis DSN 支持密码
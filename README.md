MQK
====

MQK是一个简单、高性能的PHP后台任务框架。在单核心的VPS上每秒可以处理`20,000+`数据量，在容错模式下每秒可处理`6,000+`。

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

也可以使用命令行测试。`mqk invoke \\MQK\\Test\\Calculator::sum 1 1`

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
[2017-07-11 08:14:52] 14327 .NOTICE: Master work on 14327 [] []
[2017-07-11 08:14:56] 14331 .INFO: Job finished and result is 2 [] []
```

Result is 2 说明函数执行完成，函数的返回值是2。

## K::invoke($functionName, ...$arguments)

`K::invoke`传入两个参数，第一个是函数的路径。例如`\\MQK\\Test\\Calculator::sum`，
其中`\\MQK\\Test`是`namespace`，`Calculator`是类名，`sum`是静态方法名，所以有`::`静态方法分隔符。

也可以是 `K::invoke('add', 1, 1)`。

不能调用实例对象。

```php
class Calculator
{
    public function sum($a, $b)
    {
        return $a + $b;
    }
}

$calculator = new Calculator();
$calculator->sum(1, 1);
```

上面的方法是无法成功调用的。应该定义成静态方法。

## Install

```shell
$ composer require "fatrellis/mqk:0.0.2-alpha"
```

## 依赖

- php 5.6
- php-redis
- redis-server

## 开发状态

开发中，不推荐用在生产环境

### 坑
- 超时重试可能有些问题，未严格验证。
- 未进行严格的测试，可能存在各种问题。
- 函数参数以json格式进行序列化，不能使用对象

## 测试

### 性能测试

在mbp的i7移动版cpu上的测试结果是

进行写入压力测试。先用`invoke`命令批量写入100,000条数据。然后用`monitor`命令观察任务的观察情况。

```shell
$ vendor/bin/mqk invoke \\MQK\\Test\\Calculator::sum 1 1 --invokes 500000 --workers 10

Options
    --invokes -i 总的调用次数，例如1000次调用
    --workers -w 并发的进程数量
```

`monitor`每秒检查并输出剩余的队列数量，可以了解到队列消费的数据情况。

```shell
$ mqk monitor
2017-07-07 03:55:07,4000
2017-07-07 03:55:08,0

$ bin/mqk monitor --redis redis-dsn://192.168.0.100

Options
    --redis-dsn -s Redis服务器的DSN redis://127.0.0.1:1234
```

```shell
$ vendor/bin/mqk run --burst --fast --quite -w 100
```

### 测试任务超时

call `sumTimeout` 函数。会延迟2秒导致任务失败。失败后重试然后执行成功。

```
$ vendor/bin/mqk invoke \\MQK\\Test\\Calculator::sumTimeout 1 1
```

```
$ vendor/bin/mqk run
Sum testsleep 2 will timeout
[2017-07-13 15:01:10] 24533 MQK\Worker\WorkerConsumer.WARNING: The job 72d81ec7d44b2ee460a3c4ab8e6283e4 timed out for 1 seconds. [] []
```

## Issues

- [x] 任务超时再次重试存在BUG

## TODO

- [x] `bin/mqk monitor` 增加redis dsn参数配置redis
- [x] 主进程退出退出的时候先退出其他子进程
- [x] 增加burst模式，队列为空时进程退出不在服务
- [ ] 通过信号增加和减少Worker的数量
- [x] Redis DSN 支持密码
- [ ] `mqk monitor` 支持 `--interval 1` 选项

## Burst 模式

```shell
$ vendor/bin/mqk run --burst -w 2
[2017-07-11 08:11:37] 14309 .NOTICE: Master work on 14309 [] []
[2017-07-11 08:11:37] 14313 .INFO: Worker 14313 is quitting. [] []
[2017-07-11 08:11:37] 14314 .INFO: Worker 14314 is quitting. [] []
```
burst模式下当所有任务执行完成后mqk进程会退出。非burst模式会一直运行到进程被终止。

## 安静模式

非安静模式下会输出函数执行的结果。

```shell
$ vendor/bin/mqk run
[2017-07-11 08:14:52] 14327 .NOTICE: Master work on 14327 [] []
[2017-07-11 08:14:56] 14331 .INFO: Job finished and result is 2 [] []
```

安静模式下，不输出结果。在并发很高的情况下，输出内容的打印会影响性能。

## 命令说明

## Run

```shell
$ vendor/bin/mqk run
选项
    --workers -w 并发的进程数量
    --burst -b Burst模式
    --quite -q 安静模式，不输出函数的执行结果
    --fast -f 开启技术模式，任务执行异常不会在试。
    --verbose -vvv 打印程序的调试信息
```

## Invoke

invoke命令用命令行迅速调用php函数。php命名空间的分隔符用使用 `\\` 代替 `\`，因为在CLI下需要转义。

```shell
$ vendor/bin/mqk invoke \\MQK\\Test\\Calculator::sum 1 1
[2017-07-11 08:14:56] 14331 .INFO: Invoke \\MQK\\Test\\Calculator::sum [] []
[2017-07-11 08:14:56] 14331 .INFO: ['json argument'] [] []
```

```shell
$ vendor/bin/mqk invoke function argument ...
选项
    --workers   -w 启动函数执行的工作进程数
    --invokes   -i 总的调用次数，例如1000次调用
    --redis-dsn -s Redis服务器的连接DSN。如果连接Redis cluster请使用`--cluster`参数。
    --cluster   -c 连接Redis Cluster。
    --ttl       -t 任务执行的超时时间，如果设置了 --ttl 10，当程序执行超过10秒将会认为是超时的。
```

## Monitor

按每秒输出队列中剩余的任务数量

```shell
$ vendor/bin/mqk monitor
2017-07-11 08:30:23,100000
2017-07-11 08:30:24,10000
2017-07-11 08:30:25,0
```

```shell
$ vendor/bin/mqk monitor
选项
    --redis-dsn -s Redis服务器的连接DSN。如果连接Redis cluster请使用`--cluster`参数。
    --cluster   -c 连接Redis Cluster。
```

## FAQ

运行在fast模式下，任务失败是不会再次重试。
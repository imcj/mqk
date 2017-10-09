错误处理
========

MQK消费者会 catch 住`\Exception`，并在控制台输出。也可以使用sentry捕获异常。

Sentry
-------

启动 `vendor/bin/mqk --config config.yml` 指定配置文件，添加sentry项。

```
# config.yml

sentry: sentry_dsn
```

或者通过命令行参数指定
```shell
$ vendor/bin/mqk --sentry sentry://host
```


错误处理
-------

在配置文件中设置 `error_handlers`列表，每一条一个类的名字，实现 `MQK\Error\ErrorHandler` 接口。

```yaml
error_handlers:
  - App\ExceptionHandler
```

```
use MQK\Error\ErrorHandler;

class ExceptionHandler implements ErrorHandler
{
    public function got(\Exception $exception)
    {
        
    }
}
```

崩溃重启
-------

消费者进程如果因为某些原因奔溃，主进程会再次启动一个进程。在Unix系统中，进程退出会触发一个SIGCHLD信号，
主进程监听该信号，子进程退出时，会启动一个新的进程继续工作。

错误重试
--------

MQK中的消息处理有一个默认的超时时间，当超过这个时间之后，MQK会认为消息处理失败。MQK如果意外奔溃或者主机
宕机，为避免宕机照成消息丢失。

MQK会将每一个消息写入到一个执行中的列表，消息完成后从改列表中删除。如果出现超过时间未删除的消息，MQK会认为
该消息的处理消费者进程已经奔溃。

可以在配置文件中全局关闭超时重试。

```
# config.yml
fast: false
```

或者

```php
$message = \K::invokeLate('Calculator::sum', 1, 1);
$message->setRetry(5)
```
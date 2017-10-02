异常
====

## 超时重试

进程在执行的过程中crash，未能通知MQK消息正确完成。消息有执行过期时间，过期后会重试。

有一种办法可以让MQK的消息模拟进程crash，用于测试超时重试。哪就是在消息中抛出`SkipFailureRegistryException`异常。

```shell
$ bin/mqk invoke -vvv \\MQK\\Test\\Calculator::sumCrash 1 1 2 --ttl 1
```
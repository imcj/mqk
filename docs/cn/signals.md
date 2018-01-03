信号
====

MQK支持在服务运行的时候启动新的进程或关闭进程久的进程、平滑重启、平滑退出等操作。

所谓平滑退出是指当服务在运行的过程中，任务可能还未完成，主进程会等待所有任务完成后才退出进程。消费者退出
时间过长，超过30秒，主进程会强制退出该消费者进程。

信号支持的灵感来自于`unicorn`，在发送信号的时候需要先知道主进程的进程id。进程id在主进程启动的时候会输出到控制台，也可以
通过指定pid文件，在pid文件中查询进程id。亦或许通过`ps`命令查询进程id。

发送信号
-------

可以通过`kill`指令向进程发送信号，`kill -SIGKILL pid`可以向进程发送强制杀死进程的信号。

主进程平滑退出
--------------

主进程通知消费者进程退出并等待30秒。如果消费者进程任然没有完成任务，将会强制退出。

```
$ kill -SIGTERM pid
```

主进程强制退出
-------------

主进程强制退出可能会消息执行到一半的时候失败。会在新的MQK进程启动并且该消息过期后再次执行消息。

```
$ kill -SIGQUIT pid
$ kill -SIGINT pid
```

平滑重启
---------

用新的配置文件启动新的消费者进程，并将旧进程平滑退出。

```
$ kill -SIGUSR pid
```

增加新的消费者进程
-----------------

发送 SIGTTIN 信号增加新的消费者进程。

```
$ kill -SIGTTIN pid
```

减少消费者进程的数量
-------------------

发送 SIGTTOU 信号减少一个消费者进程。

```
$ kill -SIGTTOU pid
```
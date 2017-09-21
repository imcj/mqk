Example event
==============

这里演示事件机制，下面的演示路径在mqk根目录为例子。

事件派发和监听
-------------


注意，这里没有使用配置文件

```shell
$ bin/mqk run --bootstrap example/event/bootstrap.php

hello world!!!
```

2. 派发事件

事件派发的代码请查看`dispatch_event.php`文件。

```shell
php example/event/dispatch_event.php
```

Event subscriber
-----------------

1. 启动消费者进程

`bootstrap_subscriber.php` 定义了 Subscriber

```shell
$ bin/mqk run --bootstrap example/event/bootstrap_subscriber.php

listen on subscriber
```

2. 派发事件

```shell
php example/event/dispatch_event.php
```
异步RPC
---------

默认发送到default队列。

```shell
$ php example/invoke/invoke_no_reply.php
```

异步RPC发送到high队列
--------------------

```shell
$ php example/invoke/invoke_no_reply_to_queue_high.php
```

异步RPC异常
----------

```shell
$ php example/invoke/invoke_no_reply_exception.php
```

异步RPC异常发送到high队列
-----------------------

```shell
$ php example/invoke/invoke_no_reply_exception_to_queue_high.php
```
健康状态
========

MQK将工作进程的信息保存在Redis中，通过hgetall mqk:status:unqid查询到进程的信息。

```
hget mqk:worker:status:unqid
1) "last_updated_at"
2) "1000000000"
3) "last_updated_at_human"
4) "2017-01-01 00:00"
5) "status"
6) "started"
7) "consumed"
8) "100"
9) "duration"
10) "100"
11) "process_id"
12) "1"
13) "id"
14) "uniqid"

expire mqk:worker:status:unqid 600
```

status
-------

status 保存了工作进程的状态，如果任务出现异常，可以观察工作进程的执行情况。

```
const STARTED = "started";
const WILL_DEQUEUE = "will_queue";
const DID_DEQUEUE = "did_queue";
const EXECUTING = "executing";
const EXECUTED = "executed";
const QUITTING = "quitting";
const QUITED = "quited";
```

**STARTED**

工作进程完成启动，一般情况下看不到这个状态。工作进程启动后会立刻去队列查询消息。

**WILL_QUEUE**

即将查询队列内的消息。

**DID_QUEUE**
 从队列中查询到消息。

 **EXECUTING**
 
 整在准备处理消息，如果消息的处理时间很长，那么在执行完成之前将一直都是EXECUTING这个状态。

 **EXECUTED**

 消息处理完毕。

 **QUITTING**

 消费者进程整在退出，此时可能还存在消息没有还在EXECUTING状态。等待消息执行完成后退出。

 **QUITTED**

 退出表示已经工作者已经准备并退出。
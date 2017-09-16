健康状态
========

MQK将工作进程的信息保存在Redis中，通过hgetall mqk:status:unqid查询到进程的信息。

```
last_update_at: 1100000
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
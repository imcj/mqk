Process health
===============

MQK will work process information stored in Redis, through hgetall mqk: status: unqid query to the process of information.

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

status saved the status of the work process, if the task is abnormal, you can observe the implementation of the work process.

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

Work process to complete the start, under normal circumstances do not see this state. Work process will immediately start after the queue query message.

**WILL_QUEUE**

Query the message in the queue.

**DID_QUEUE**

Query the message from the queue.

**EXECUTING**
 
The whole process is ready to deal with the message, if the message processing time is very long, then the implementation will be completed before the EXECUTING this state.

**EXECUTED**

Message executed

**QUITTING**

The consumer process is always in the exit, this time there may still be messages are still still EXECUTING state. Wait for the message to exit after exiting.

**QUITTED**

The exit indicates that the worker has been prepared and exited.
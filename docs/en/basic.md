Basic
======

MQK is a background task execution framework. Through the deployment of multiple MQK for horizontal scaling.

Producer
---------

The producer saves the message in the redis list structure, and the contents of the consumer pull are treated as messages. The event class's message stores the full path of the event class, including the namespace and class name,
It also includes class attribute information for the event class. When the producer enters the message, the MQK serializes the information into a JSON structure and then saves it to the list. MQK uses `symfony / serializer`
Serialization, so please save it as a simple JSON structure. (integer string array hash bool).

Redis
------

Redis saves queue information, message meta information and health status. The meta information of the message is stored in the queue when it is included in the queue, and it is stored in the KV in other states, so the storage of KV
The performance of the message queue. Health status stored in the hash kv inside, you can query these kv understand the health of the process. Subsequent versions will develop web ui to facilitate viewing the health of the process.

Consumer
---------

One reason consumers are running in multiple processes is that they can take advantage of multiple processes to enhance performance. When the consumer process crashes, the main process will start a new process to continue working. In the expansion of the time,
If the computing resources are not enough, you can run by adding a new machine MQK instance, MQK can simply solve the problem of expansion by adding machines. However, if the amount of information is too large, the pressure is in Redis
On the need to split the business distribution to more Redis instance.
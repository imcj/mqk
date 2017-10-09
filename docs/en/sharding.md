Sharding
=========

MQK can only use a Redis instance.

MQK expansion in the subsequent version will be used more Redis instance deployment, the message queue and message Meta information is divided into different instances. A separate queue instance can effectively improve the throughput of MQK. This feature will be supported in subsequent releases.

The Meta instance is intended to use Redis's Cluster mode, which allows you to slice Meta into different machines. Specific performance improvements require actual testing, please check Redis cluter related documentation to understand.

MQK process expansion is relatively simple, just need to start more MQK can be horizontal expansion is friendly.

There is also a way to deploy MQKs in a modular way, with each module using a separate MQK, which allows for a modular partitioning of the running system and consequently better performance.
Signals
========

MQK support in the service when running a new process or close the process of a long process, smooth restart, smooth exit and other operations.

The so-called smooth exit means that when the service is running, the task may not yet complete, the main process will wait for all tasks to complete before exiting the process. Consumers quit
Time is too long, more than 30 seconds, the main process will force the exit of the consumer process.

Signal support from the `unicorn`, when sending the signal need to know the process of the main process id. The process id is output to the console when the main process starts
Query the process id in the pid file by specifying the pid file. Or through the `ps` command to query the process id.

Send signal
------------

Can be sent to the process through the `kill` command signal,` kill -SIGKILL pid` can send a process to force the process of killing the signal.

The main process graceful quit
-------------------------------

The main process notifies the consumer process to exit and wait 30 seconds. If the consumer process does not complete the task, will be forced to withdraw.

```
$ kill -SIGTERM pid
```

The main process forces to exit
--------------------------------

The main process to force the exit may be the implementation of the message to half of the failure. The message is executed again after the new MQK process starts and the message expires.

```
$ kill -SIGQUIT pid
$ kill -SIGINT pid
```

Graceful restart
-----------------

Start the new consumer process with the new configuration file and exit the old process smoothly.

```
$ kill -SIGUSR pid
```

Increase the new consumer process
-----------------------------------

Send SIGTTIN signal to add new consumer process.

```
$ kill -SIGTTIN pid
```

Reduce the number of consumer processes
----------------------------------------

Send SIGTTOU signal to reduce a consumer process.

```
$ kill -SIGTTOU pid
```
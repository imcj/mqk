Process
========

MQK support multi-process mode of operation, the specific multi-process application method View the Advanced section to understand the application of multi-process. MQK uses the `fork` function to generate the specified number of child processes, and the corresponding PHP function is
`pcntl_fork`, this mode can only run in the Linux environment, if the Windows environment will automatically enter the development mode. In development mode, the multi-process mode is turned off and a single process is used.

Work process guardian
----------------------

The main process of MQK will listen to the `SIGCHLD` signal after the daemons process, which informs the main process from the operating system that the process exits the event.

When the main process that the child process after the exit, will start a new process to continue the current work. When the child process exits, the main process goes to the reap process. reap this process is to prevent zombies
The process is generated. Not reap the process will become a zombie process, too much zombie process will lead to the operating system crash.


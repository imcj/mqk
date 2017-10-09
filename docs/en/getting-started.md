Getting started
===============

Asynchronous RPC
-----------------

1. Install composer package

```php
$ composer require mqk/mqk
```

2. Define an asynchronous RPC method

```php
class Calculator
{
    public static function sum($a, $b)
    {
        return $a + $b;
    }
}
```

3. Start the consumer process

-vvv print detail log

```php
$ vendor/bin/mqk run -vvv
```

4. Execute asynchronous RPC

```php
\K::invoke('Calculator::sum', 1, 2);
```

More
----

Asynchronous RPC mode is more suitable for sending such a scenario like mail, usually like a message to send such a scene without the need to immediately return the results to the user and the implementation of the event is relatively long.

There are other scenarios that recommend the use of events, when we need to decouple the system, for example, a resource A changes, another resource B depends on the resource A need to maintain the final consistency of the data.
Distribute a modification of the A resource, and then monitor the consistency of the event maintenance data.

[Event] (event.md)

Advanced use describes how to handle multiple different queues, and how effective use of concurrency features in MQK.

[Advanced use] (advanced_options.md)
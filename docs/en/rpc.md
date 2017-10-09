RPC
=======

Asynchronous blocking RPC calls
--------------------------------

Asynchronous RPC can be obtained in addition to the results of the return. You can also concurrently execute multitasking, execute multiple tasks that are not dependent on each other, and reduce the response time of the application.

- [RPC](#rpc)
    - [Asynchronous blocking RPC calls](#asynchronous-blocking-rpc-calls)
    - [Asynchronous does not block RPC calls](#asynchronous-does-not-block-rpc-calls)
    - [Asynchronously call RPC](#asynchronously-call-rpc)

```php
use \MQK\Queue\Invoke;
use MQK\Queue\Invokes;

$invoke = K::invokeAsync(
    new Invokes(
        new Invoke('a', 'Calculator::sum', 1, 1),
        new Invoke('b', 'Calculator::sum', 2, 2),
        new Invoke('c', 'Calculator::sum', 3, 3),
        new Invoke('d', 'Calculator::sum', 4, 4)
    )
);
```

```new Invoke('a', 'Calculator::sum', 1, 1)```

Invoke has three parameters, the first parameter sets the key name of the RPC, the second argument is the method name, and the remaining argument is the parameter of the method call.

2. Query result


```php
$invoke->wait();

extract($invoke->returns());
```

`$invoke->wait();` Waiting for all RPC tasks to finish

`extract($invoke->returns());`， `returns()` Will return multiple RPC results to hash table, extract the hash key as the current function of the variable.

```php
assert($a == 2);
assert($b == 4);
assert($c == 6);
assert($d == 8);
```

Asynchronous does not block RPC calls
--------------------------------------

```php
class Calculator
{    
    public function sum($a, $b)
    {
        return $a + $b;
    }
}
```

Asynchronously call RPC
------------------------

```php
\K::invoke('Calculator::sum', 1, 2);
```
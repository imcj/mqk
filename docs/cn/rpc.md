RPC
=======

异步阻塞RPC调用
--------------

异步RPC除了可以在执行的时候获取到返回结果以外。还可以并发多任务执行，将多个相互不依赖的任务同时执行，减少应用程序的响应时间。

1. RPC调用

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

Invoke有三个参数，第一个参数设置RPC的键名，第二参数是方法名，剩余的参数是方法调用的参数。

2. 查询结果


```php
$invoke->wait();

extract($invoke->returns());
```

`$invoke->wait();`等待所有的RPC任务完成。

`extract($invoke->returns());`， `returns()`方法将多个RPC结果返回为hash表，extract将hash的键保存为当前函数的变量。

```php
assert($a == 2);
assert($b == 4);
assert($c == 6);
assert($d == 8);
```

异步不阻塞RPC调用
-----------------

```php
class Calculator
{    
    public function sum($a, $b)
    {
        return $a + $b;
    }
}
```

异步调用RPC

```php
\K::invoke('Calculator::sum', 1, 2);
```
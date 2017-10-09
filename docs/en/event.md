Event
======

An event is a tool for decoupling between services in a microarchitecture. Using MQK events, you can respond to an event and execute a piece of code in the listener.

1. Dispatch event

```php
$event = new UserCreatedEvent(1);
\K::dispatch("user.created", $event);
```

2. Define event

```php
class UserCreatedEvent
{
    const NAME = "user.created";
    
    /**
     * User id
     *
     * @var integer
     */
    public $id;
    
    public function __construct($id)
    {
        $this->id = $id;
    }
}
```

3. Listener

```php
# bootstrap.php

\K::addEventListener(UserCreatedEvent::NAME, function (UserCreatedEvent $event) {
    // handle user event
});
```

4. Start

```php
$ bin/mqk --bootstrap bootstrap.php
```

## Subscriber

1. Define subscriber

```
class UserSubscriber
{
    /**
     * @Listener(UserCreatedEvent::NAME)
     */
    public function onCreated(UserCreatedEvent $event)
    {
        // 处理用户创建事件
    }
}
```

2. Subscribe

Specify `bootstrap` in the configuration file and add the subscribers to` bootstrap` '.

```php
# bootstrap.php

\K::addSubscriber(new UserSubscriber());
```

3. Start

```php
$ bin/mqk --bootstrap bootstrap.php
```
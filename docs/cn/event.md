事件
====

事件是微架构中服务间解耦的利器。使用MQK的事件，你可以响应一个事件，在监听中执行一段代码。

1. 派发事件

```php
$event = new UserCreatedEvent(1);
\K::dispatch("user.created", $event);
```

2. 定义事件

```php
class UserCreatedEvent
{
    const NAME = "user.created";
    
    /**
     * 用户Id
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

3. 监听事件

```php
# bootstrap.php

\K::addEventListener(UserCreatedEvent::NAME, function (UserCreatedEvent $event) {
    // 处理用户创建事件
});
```

4. 启动

```php
$ bin/mqk --bootstrap bootstrap.php
```

## 订阅者

1. 定义Subscriber

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

2. 添加 Subscriber

在配置文件中指定`bootstrap`，并在`bootstrap`中添加订阅者。

```php
# bootstrap.php

\K::addSubscriber(new UserSubscriber());
```

3. 启动

```php
$ bin/mqk --bootstrap bootstrap.php
```
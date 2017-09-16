事件
====

事件是微架构中服务间解耦的利器。

1. 派发事件

```php
$event = new UserCreatedEvent(1);
$event->dispatch();
```

2. 定义事件

```php
class UserCreatedEvent
{
    use DispatcherTrait;
    
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
\K::addEventListener(UserCreatedEvent::NAME, function (UserCreatedEvent $event) {
    // 处理用户创建事件
});
```

## 订阅者

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

```php
\K::addSubscriber(new UserSubscriber());

```
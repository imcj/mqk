<?php
use MQK\Queue\Message\MessageDAO;
use MQK\Queue\Invokes;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\Queue;
use MQK\Queue\QueueFactory;
use MQK\RedisFactory;
use Symfony\Component\EventDispatcher\Event;

class K
{
    /**
     * @var Queue
     */
    private static $queue;

    /**
     * @var \MQK\Queue\MessageAbstractFactory
     */
    private static $messageFactory;

    private static $connection;

    private static $queueFactory;

    /**
     * @var MessageDAO
     */
    private static $messageDAO;

    public static function setup($config)
    {
    }

    public static function invoke($func, ...$args)
    {
        $message = new \MQK\Queue\MessageInvokable(uniqid());
        $payload = new stdClass();
        $payload->func = $func;
        $payload->arguments = $args;
        $message->setPayload($payload);

        self::defaultQueue()->enqueue($message);

        return $message;
    }

    public static function invokeSync($invokes)
    {
        foreach ($invokes as $invoke) {
            $func = $invoke['func'];
            $args = $invoke['arguments'];

            // 设置一个KV保存所有Invoke的信息，Invoke每次执行完成的时候判断是否执行完成。
            // 一个队列扫描，如果该队列存在消息超时则立刻响应InvokeSync
            // 沿用以前的消息扫描机制，判断消息的类型如果是同步类型做不同的处理
            // 估计需要一个周的时间。

            $message = new \MQK\Queue\Message(uniqid());
            $payload = new stdClass();
            $payload->func = $func;
            $payload->arguments = $args;
            $message->setPayload($payload);
        }

        self::defaultQueue()->enqueue($message);

        return $message;
    }

    public static function invokeAsync(Invokes $invokes)
    {
        $connection = self::createConnection();
        $messageDAO = self::createMessageDAO();
        foreach ($invokes->invokes() as $invoke) {
            $invokes->setConnection($connection);
            $invokes->setMessageDAO($messageDAO);
            self::defaultQueue()->enqueue($invoke->createMessage());
        }

        return $invokes;
    }


    public static function dispatch(Event $event, $ttl = -1)
    {
        $message = self::createMessageFactory()->messageWithEvent($event);
        if ($ttl > -1)
            $message->setTtl($ttl);
        self::defaultQueue()->enqueue($message);

        return $message;
    }

    /**
     * 添加事件监听
     *
     * @param string $name
     * @param \Closure $callback
     */
    public static function addListener($name, $callback)
    {
        \MQK\Queue\MessageEventBus::shared()->addListener($name, $callback);
    }

    static function createConnection()
    {
        if (null == self::$connection) {
            $factory = RedisFactory::shared();
            self::$connection = $factory->createConnection();
        }
        return self::$connection;
    }

    static function createMessageFactory()
    {
        if (null == self::$messageFactory)
            self::$messageFactory = new MessageAbstractFactory();
        return self::$messageFactory;
    }

    static function createQueueFactory($connection)
    {
        if (null == self::$queueFactory)
            self::$queueFactory = new QueueFactory($connection, self::createMessageFactory());
        return self::$queueFactory;
    }

    static function createMessageDAO()
    {
        if (null == self::$messageDAO)
            self::$messageDAO = new MessageDAO(self::createConnection());

        return self::$messageDAO;
    }

    static function defaultQueue()
    {
        if (null == self::$queue) {
            self::$queue = self::createQueueFactory(self::createConnection())->createQueue("default");
        }

        return self::$queue;
    }
}
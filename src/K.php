<?php
use MQK\Job;
use MQK\Queue\Queue;
use MQK\Queue\RedisQueue;
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

    public static function setup($config)
    {
    }

    static function job($func, $args)
    {
        $job = new Job(null, $func, $args);
        $job->setConnection(self::defaultQueue()->connection());

        return $job;
    }

    public static function invoke($func, ...$args)
    {
        $message = new \MQK\Queue\Message(uniqid());
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

    public static function dispatch(Event $event, $ttl = -1)
    {
        $message = self::messageFactory()->messageWithEvent($event);
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

    public static function delay($second, $func, ...$args)
    {
        $job = self::job($func, $args);
        $job->setDelay($second);
        self::defaultQueue()->enqueue($job);

        return $job;
    }

    static function defaultQueue()
    {
        if (null == self::$queue)
            self::$queue = (new \MQK\Queue\QueueFactory())->createQueue("default");

        return self::$queue;
    }

    static function messageFactory()
    {
        if (self::$messageFactory == null)
            self::$messageFactory = new \MQK\Queue\MessageAbstractFactory();

        return self::$messageFactory;
    }
}
<?php
use MQK\Config;
use MQK\Queue\Invokes;
use MQK\Queue\Message\MessageDAO;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\Queue;
use MQK\Queue\QueueFactory;
use Symfony\Component\EventDispatcher\Event;
use MQK\Queue\MessageEventBus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;
use MQK\Queue\Message;

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

    /**
     * @var Config
     */
    private static $config;

    private static $initializedConfig = false;

    public static function setupConfig($yamlPath)
    {
        $conf = Config::defaultConfig();

        if (!file_exists($yamlPath)) {
            throw new Exception("Yaml not found");
        }

        $parseProcessor = new \MQK\YamlConfigProcessor(
            Yaml::parse(file_get_contents($yamlPath)),
            $conf
        );
        $parseProcessor->process();
    }

    public static function invoke($func, ...$args)
    {
        $defaultQueue = self::config()->defaultQueue();
        return self::invokeTo($defaultQueue, $func, ...$args);
    }

    public static function invokeTo($queueName, $func, ...$args)
    {
        $message = new \MQK\Queue\MessageInvokable(uniqid());
        $payload = new stdClass();
        $payload->func = $func;
        $payload->arguments = $args;
        $message->setPayload($payload);
        $message->setQueue($queueName);

        self::defaultQueue()->enqueue($message->queue(), $message);

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
            $message = $invoke->createMessage();
            self::configDefaultMessage($message);
            self::defaultQueue()->enqueue($message->queue(), $message);
        }

        return $invokes;
    }


    public static function dispatch($eventName, Event $event, $ttl = -1)
    {
        $message = self::createMessageFactory()->messageWithEvent($eventName, $event);
        self::configDefaultMessage($message);
        if ($ttl > -1)
            $message->setTtl($ttl);
        self::configDefaultMessage($message);
        self::defaultQueue()->enqueue($message->queue(), $message);

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

    /**
     * Add event subscriber
     *
     * @param EventSubscriberInterface $subscriber
     */
    public static function addSubscriber(EventSubscriberInterface $subscriber)
    {
        MessageEventBus::shared()->addSubscriber($subscriber);
    }

    static function configDefaultMessage(Message $message)
    {
        $config = self::config();
        $defaultQueue = $config->defaultQueue();
        if ($message->queue() == null)
            $message->setQueue($defaultQueue);

        if (!in_array($defaultQueue, self::config()->queues())) {
            if (empty($config->queues())) {
                throw new \Exception("Queue list empty");
            }
            $message->setQueue(self::config()->queues()[0]);
        }
    }

    static function createConnection()
    {
        if (null == self::$connection) {
            $config = Config::defaultConfig();
            self::$connection = new \MQK\RedisProxy($config->redis());
            self::$connection->connect();
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
            self::$queue = new \MQK\Queue\RedisQueue(
                self::createConnection(),
                self::config()->queuePrefix()
            );
        }

        return self::$queue;
    }

    static function config()
    {
        if (null == self::$config)
            self::$config = Config::defaultConfig();
        return self::$config;
    }
}